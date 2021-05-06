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

use SilverStripe\Forms\FieldList;
use SilverStripe\ORM\DataObject;
use SilverStripe\SiteConfig\SiteConfig;

class AllowedIPAddress extends DataObject
{
    private static $table_name = 'AllowedIPAddress';

    private static $singular_name = 'Allowed IP Address';

    private static $plural_name = 'Allowed IP Addresses';

    private static $db = [
        'Address'          => 'Varchar(255)',
        'PhysicalLocation' => 'Varchar(255)'
    ];

    private static $has_one = [
        'SiteConfig' => SiteConfig::class
    ];

    private static $summary_fields = [
        'Address',
        'PhysicalLocation'
    ];

    private static $field_labels = [
        'Address'          => 'IP Address',
        'PhysicalLocation' => 'Physical Location'
    ];

    /**
     * @return FieldList
     */
    public function getCMSFields()
    {
        $fields = parent::getCMSFields();

        $fields->removeByName(['SiteConfigID']);

        return $fields;
    }
}
