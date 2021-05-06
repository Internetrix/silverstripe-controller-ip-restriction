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

use SilverStripe\ORM\DataExtension;
use SilverStripe\Forms\FieldList;
use SilverStripe\Security\Permission;
use SilverStripe\Forms\CheckboxField;

class MemberExtension extends DataExtension
{
    private static $db = [
        'CanBypassIPRestriction' => 'Varchar(255)'
    ];

    /**
     * @param FieldList $fields
     */
    public function updateCMSFields(FieldList $fields)
    {
        if ($this->owner->ID > 0 && Permission::checkMember($this->owner, 'ADMIN')) {
            $fields->insertAfter('DirectGroups',
                    CheckboxField::create('CanBypassIPRestriction', 'Allow Bypass of IP Restriction')
            );
        }

        return $fields;
    }
}
