# Introduction


This module allows SilverStripe developers to restrict access to arbitrary-defined controllers, according to a User's IP address. Requests to specific Controllers will be denied if a user's IP does not match the provided list of allowed IP addresses.


Allowed IP addresses can be set via an environment variable or via the SiteConfig.
Common use cases of the module includes:
- Restricting access to the CMS admin section 
- Restricting access to the dev/tasks admin.

## Requirements
* SilverStripe CMS ^4

## Installation & Configuration
1. Install the module via composer:
```
composer require internetrix/silverstripe-controller-ip-restriction
```

2. Set the following variables for `Internetrix\CMSAdminIPRestriction\AllowedIPMiddleware` in config:
    - enabled: Set this to true to enable this module's AllowedIPMiddleware
    - restricted_controllers: A list of controllers you want to be restricted by IP addresses
   

In the following example, we are restricting access to the CMS admin as well as the `/dev` controller.
```
Internetrix\CMSAdminIPRestriction\AllowedIPMiddleware:
  enabled: true
  restricted_controllers:
    - SilverStripe\Admin\AdminRootController
    - SilverStripe\Dev\DevelopmentAdmin
``` 

3. Define which IP addresses are allowed in `.env` file, using a `SS_ADMIN_ALLOWED_IPS` variable. For multiple IP addresses, use a comma-limited list.
```
SS_ADMIN_ALLOWED_IPS='123.0.0.1,248.1.1.1'
```
- Additional allowed IP addresses can also be set by a CMS admin via the CMS SiteConfig, under the `Allowed IPs` tab. Simply create a new record with an exact IP address and label it with the corresponding physical location (used for audit purposes)

## Bypass IP restriction
- If a certain user is an Admin, they can bypass the IP restriction check if the `CanBypassIPRestriction` database field is set to true. This can be set when editing the member in the CMS and is only applied if the User remains a CMS Administrator. 
<br><br>
  <b>Important:</b> Due to race conditions, this features requires a user to be first logged-in so that the
  `CanBypassIPRestriction` value for the user can be checked. If the CMS admin or another Controller is restricted, a user must first log-in to SilverStripe via a non-restricted page (i.e A Only Logged-in Users Can View Page) before the IP Bypass will be applied.

## Troubleshooting
- If no IP address is specified in `.env` or via the `SiteConfig`, the restricted controllers will be inaccessible.

## Todo
- Currently, IP matching is based on exact matching IP address. Range, wildcard and CIDR is currently not supported and will included in the future. Pull requests welcome!
