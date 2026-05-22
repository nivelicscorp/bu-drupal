CONTENTS OF THIS FILE
---------------------
 * Introduction
 * Requirements
 * Installation
 * Configuration
 * Send Test Email


INTRODUCTION
------------

The Mailchimp Transactional module integrates Drupals mail system with Mailchimp
Transactional (formerly Mandrill), a transactional email delivery API from the
folks behind MailChimp. This module allowes transactional emails from Drupal to
be sent with the Mailchimp Transactional service.

Learn more at [Mandrillapp.com](http://mandrillapp.com)


REQUIREMENTS
------------
This module requires the [Mail System module](https://drupal.org/project/mailsystem)

It also relies upon the [Mailchimp Transactional PHP Library](https://github.com/mailchimp/mailchimp-transactional-php)


INSTALLATION
------------

 * Install as you would normally install a contributed Drupal module,  
ideally managed by [Composer](https://www.drupal.org/docs/extending-drupal/installing-modules#s-add-a-module-with-composer)

 * Composer will grab a copy of the Mailchimp Transactional PHP Library and
place it with the other non-Drupal libraries, usually in the `/vendor` folder.

 * If the project is not managed by Composer, be sure to add a copy of the  
[Mailchimp Transactional PHP library](https://github.com/mailchimp/mailchimp-transactional-php) to your project.  
This library is called by through the `\MailchimpTransactional\ApiClient` class.

 * Enable the module from Admin » Extend or with  
`drush en mailchimp_transactional`.

MIGRATION FROM MANDRILL
-----------------------
If your site is using the [Mandrill module](https://www.drupal.org/project/mandrill),  
this is the upgrade path for Drupal 9 and continued support.

First, you'll want to update Mandrill to version 8.x-1.3 and Drupal to 9.

When Mailchimp Transactional is installed, it will look for the Mandrill module
and copy the existing Mandrill configuration, including role permissions and 
mailsystem plugins.

The same is true for the Activity, Reports, and Template submodules.

Please verify the configurations of Mailchimp Transactional, its submodules,
and Mailsystem.

Finally, turn off Mandrill and retire it from your site.

CONFIGURATION
-------------

1. [Acquire a Mailchimp Transactional API key](https://mandrillapp.com/settings)
2. Set Mailchimp Transactional API Key in Admin » Configuration » Web Services
(`admin/config/services/mailchimp_transactional`) and Save to verify the key.

A saved, valid API key must be provided before the remaining options are shown:

 * Email Options
    - From address: The email address that emails should be sent from
    - From name: The name to use for sending (optional)
    - Subaccount: If you have configured subaccounts, 
      they can be used for sending.
    - Input format: An optional input format to apply to the message body
      before sending emails.

 * Send Options
    - Track opens: Toggles open tracking for messages
    - Track clicks: Toggles click tracking for messages
    - Strip query string: Strips the query string from URLs when aggregating  
   tracked URL data
    - Log sends that are not registered in mailsystem: Useful for configuring  
   Mail System and getting more granular control over emails coming from various  
   modules. Enable this and set the system default in Mail System to Mailchimp  
   Transactional, then trigger emails from various modules and functions on your  
   site. You'll see Mailchimp Transactional writing log messages identifying the  
   modules and keys that are triggering each email. Now you can add these keys  
   in Mail System and control each module/key pair specifically.  
   WARNING: If you leave this enabled, you may slow your site significantly and  
   clog your log files. Enable only during configuration.

 * Google Analytics
    - Domains: One or more domains for which any matching URLs will  
   have Google Analytics parameters appended to their query string.  
   Separate each domain with a comma.
    - Campaign: The value to set for the utm_campaign tracking parameter. If  
   empty, the from address of the message will be used instead.

 * Asynchronous Options
     - Queue Outgoing Messages Drops all messages sent through Mailchimp  
   Transactional into a queue without sending them. When Cron is triggered, a  
   number of queued messages are sent equal to the specified Batch Size.
     - Batch Size The number of messages to send when Cron triggers.  
   Must be greater than 0.


SEND TEST EMAIL
---------------

This tab is only available while the Mailchimp Transactional mailer is set as 
the sender in Mail System (admin/config/system/mailsystem).

The Send Test Email tab sends an email manually, through the Mailchimp 
Transactional and the configured options. The To: field will accept multiple 
addresses formatted in any Drupal mail system approved way. By configuring the 
Mailchimp Transactional Test module/key pair in Mail System, you can use this 
tool to test outgoing mail for any installed mailer.

 * Update Mail System settings:
    - Mailchimp Transactional Mail interface is enabled by using the
    [Mail System module](http://drupal.org/project/mailsystem).
    - Go to the [Mail System configuration page](admin/config/system/mailsystem) to start  
   sending emails through Mailchimp Transactional. Once you do this, you'll see  
   a list of the module keys that are using Mailchimp Transactional listed near  
   the top of the Mailchimp Transactional settings page.
    - Once you set the site-wide default (and any other listed module classes)  
   to Mailchimp Transactional Mailer, your site will immediately start using  
   Mailchimp Transactional to deliver all outgoing email.
    - Module/key pairs: The key is optional, not every module or email uses a  
   key. That is why on the mail system settings page, you may see some modules  
   listed without keys.  
   For more details about this, see the mail system configuration page  
   (admin/config/system/mailsystem).


SUBMODULES
-----------

 * Activity can be used to attach activity records to any entity with an email  
field. More details in submodule README.
 * Reports provides data in Admin » Reports:
    - A Dashboard displays a chart of volume and engagement, as well as a  
   tabular list of URL interactions.
    - Account Summary shows account information, quotas, and usage stats.
 * Template can be used to send email using a Mailchimp Template. More details  
in submodule README.


OVERRIDES
---------
Your Mailchimp Transactional API key should be treated like a password.
If you want to prevent that key from being stored in your configuration, you can
(override it)[https://www.drupal.org/docs/drupal-apis/configuration-api/configuration-override-system]:

`$config['mailchimp_transactional.settings']['mailchimp_transactional_api_key'] 
= 'xxxx'`

If you want more granular control over the display of secrets in config forms,
consider using the [Config Override Inspector module](https://www.drupal.org/project/coi).


ADVANCED OPTIONS
----------------

If you would like to use additional template (or other) Mailchimp Transactional 
API variables not implemented in this module, set them in hook_mail_alter under:
$params['mailchimp_transactional']
