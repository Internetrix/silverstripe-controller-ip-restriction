<?php

namespace Internetrix\CMSAdminIPRestriction;

/* Copyright 2021 Internetrix
This program is free software; you can redistribute it and/or
modify it under the terms of the GNU General Public License
version 2 as published by the Free Software Foundation.
This program is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
GNU General Public License for more details. */

use SilverStripe\Control\Director;
use SilverStripe\Core\Config\Configurable;
use SilverStripe\Admin\AdminRootController;
use SilverStripe\Control\HTTPRequest;
use SilverStripe\Control\HTTPResponse;
use SilverStripe\Control\Middleware\HTTPMiddleware;
use SilverStripe\Core\Config\Config;
use SilverStripe\Security\Permission;
use SilverStripe\Security\Security;
use SilverStripe\SiteConfig\SiteConfig;

/**
 * Class AllowedIPMiddleware
 * @package Internetrix\CMSAdminIPRestriction
 */
class AllowedIPMiddleware implements HTTPMiddleware
{
    use Configurable;

    /**
     * @var bool
     */
    private $enabled = false;
    /**
     * @var array
     */
    private $restrictedControllers = [];
    /**
     * @var array
     */
    private $allowedIPs = [];

    /**
     * @return array
     */

    public function getAllowedIPs(): array
    {
        return $this->allowedIPs;
    }

    /**
     * @param $allowedIPs
     * @return $this
     */
    public function setAllowedIPs($allowedIPs): AllowedIPMiddleware
    {
        if (is_string($allowedIPs)) {
            $allowedIPs = explode(',', $allowedIPs);
        }

        $subnetIps = [];
        if($allowedIPs){
            foreach ($allowedIPs as $k => $allowedIP){
                if(stripos($allowedIP, "/")){
                    $ipsInRange = $this->getEachIpInRange($allowedIP);
                    if(count($ipsInRange)){
                        $subnetIps = array_merge($subnetIps, $ipsInRange);
                        unset($allowedIPs[$k]);
                    }
                }
            }
        }

        if(count($subnetIps)){
            $allowedIPs = array_merge($allowedIPs, $subnetIps);
        }

        $this->allowedIPs = $allowedIPs;

        return $this;
    }

    /**
     * @param $cidr
     * @return array
     */
    public function getIpRange(  $cidr): array
    {

        list($ip, $mask) = explode('/', $cidr);

        $maskBinStr = str_repeat("1", $mask ) . str_repeat("0", 32-$mask );      //net mask binary string
        $inverseMaskBinStr = str_repeat("0", $mask ) . str_repeat("1",  32-$mask ); //inverse mask

        $ipLong = ip2long( $ip );
        $ipMaskLong = bindec( $maskBinStr );
        $inverseIpMaskLong = bindec( $inverseMaskBinStr );
        $netWork = $ipLong & $ipMaskLong;

        $start = $netWork+1;//ignore network ID(eg: 192.168.1.0)

        $end = ($netWork | $inverseIpMaskLong) -1 ; //ignore broadcast IP(eg: 192.168.1.255)
        return ['firstIP' => $start, 'lastIP' => $end ];
    }

    /**
     * @param $cidr
     * @return array
     */
    public function getEachIpInRange ( $cidr): array
    {
        $ips = [];
        $range = $this->getIpRange($cidr);
        for ($ip = $range['firstIP']; $ip <= $range['lastIP']; $ip++) {
            $ips[] = long2ip($ip);
        }
        return $ips;
    }

    /**
     * @param HTTPRequest $request
     * @param callable $delegate
     * @return HTTPResponse
     */
    public function process(HTTPRequest $request, callable $delegate)
    {
        $restrictedControllers = $this->getRestrictedControllers();
        if ($this->IPRestrictionApplies($request, $restrictedControllers)) {
            $controller = $request->routeParams();
            if (isset($controller['Controller']) && in_array($controller['Controller'], $restrictedControllers) ) {
                $allowedIPs = $this->allowedIPs ? $this->allowedIPs : [];
                // Merge allowed IPs added via CMS SiteConfig with IP set in .env
                $config = SiteConfig::current_site_config();
                if ($config) {
                    $customIPs = $config->AllowedIPAddresses();
                    if ($customIPs && $customIPs->count()) {
                        $allowedIPs = array_unique(array_merge($allowedIPs, $customIPs->column('Address')));
                    }
                }
                if (!$allowedIPs) {
                    return new HTTPResponse('Page not found', 400);
                }
                if (!$this->validateIPAddress($request, $allowedIPs)) {
                    return new HTTPResponse('Page not found', 400);
                }
            }
        }

        return $delegate($request);
    }

    /**
     * Checks if IP restriction needs to be applied to the current request
     * @param $request
     * @param $restrictedControllers
     * @return bool
     */
    public function IPRestrictionApplies($request, $restrictedControllers)
    {
        return !Director::is_cli()
            && $this->isEnabled()
            && !$this->canBypassIPRestriction($request)
            && !empty($restrictedControllers);
    }

    /**
     * Check if the current IP address is one of the allowed IP addresses
     * @param $request
     * @param array $allowedIPs
     * @return bool
     */
    public function validateIPAddress($request, $allowedIPs = [])
    {
        $originIP = $request->getIP();
        // Match Exact IP Address
        if ($originIP && in_array($originIP, $allowedIPs)) {
            return true;
        }

        return false;
    }

    /**
     * Check if IP restriction is enabled in YML config
     * @return bool
     */
    public function isEnabled()
    {
        $this->enabled = (bool)$this->config()->get('enabled');

        return $this->enabled;
    }

    /**
     * Return the Controller classes that the IP restrictions applies to
     * @return array
     */
    public function getRestrictedControllers()
    {
        $this->restrictedControllers = $this->config()->restricted_controllers;

        return $this->restrictedControllers;
    }

    /**
     * If a user is an ADMIN, they are allowed to potentially bypass the IP restriction check
     * if manually set in the CMS admin
     * @param $request
     * @return bool
     */
    public function canBypassIPRestriction($request)
    {
        $member = Security::getCurrentUser();
        if (Permission::check('ADMIN')) {
            return $member->CanBypassIPRestriction == 1;
        }

        return false;
    }
}
