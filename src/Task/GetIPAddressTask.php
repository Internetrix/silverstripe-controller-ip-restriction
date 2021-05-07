<?php

namespace Internetrix\CMSAdminIPRestriction;

use SilverStripe\Dev\BuildTask;

class GetIPAddressTask extends BuildTask
{
    protected $title = 'Check IP address of current user';

    public function run($request)
    {
        echo ($request->getIP());
    }
}

