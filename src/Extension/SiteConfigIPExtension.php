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

use SilverStripe\Forms\DropdownField;
use SilverStripe\Forms\FieldList;
use SilverStripe\Forms\GridField\GridField;
use SilverStripe\Forms\GridField\GridFieldConfig_RecordEditor;
use SilverStripe\Forms\TextField;
use SilverStripe\ORM\DataExtension;
use SilverStripe\SiteConfig\SiteConfig;

class SiteConfigIPExtension extends DataExtension
{
    private static $has_many = [
        'AllowedIPAddresses' => AllowedIPAddress::class
    ];

    /**
     * @param FieldList $fields
     */
    public function updateCMSFields(FieldList $fields)
    {
        $fields->addFieldsToTab('Root.AllowedIPs', [
            GridField::create('AllowedIPs', 'Allowed Admin IP Addresses', $this->owner->AllowedIPAddresses(), GridFieldConfig_RecordEditor::create()),
        ]);

        // Fix lettercase of Tab
        if ($allowedIPTabs = $fields->findTab('Root.AllowedIPs')) {
            $allowedIPTabs->setTitle('Allowed IPs');
        }

        return $fields;
    }
}
