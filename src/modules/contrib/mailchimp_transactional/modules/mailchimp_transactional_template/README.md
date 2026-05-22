MAILCHIMP TRANSACTIONAL TEMPLATES
---------------------------------

This templates submodule allows you to map a template from Mailchimp to the 
emails sent by your Drupal site. This module will insert the email content into
a designated Editable Content Area within a [Mailchimp Template](https://mailchimp.com/help/create-editable-content-areas-with-mailchimps-template-language/).

The administrative interface will allow you to choose a single content region, 
and any existing content in that region will be overwritten.

Filling in additional content areas with Drupal would require custom code

In order to use the Mailchimp Transactional Templates module, start by creating 
some templates in your Mailchimp Transactional account. Once you do, you can use
a Mailchimp Transactional Template Map to specify where in the template to place
the email content and which module/key pair should be sent using the template.
If you want to send multiple module/key pairs through the same Template, you
can make Mailchimp Transactional the default mail system and make that Template 
Map the default template, or you can clone the Template Map for each module/key 
pair and assign them individually.

INSTALLATION
------------
1. Enable the Mailchimp Transactional Template module, which requires the base  
Mailchimp Transaction module.
2. [Create some templates](https://mailchimp.com/developer/transactional/docs/templates-dynamic-content/).
3. Return to your Drupal site: 
`Admin » Configuration » Web Services » Mailchimp Transactional`
4. Under the Templates tab, Add Mailchimp Transactional Template Map
5. This map represents a single template and content region to use for email  
content.

If no content regions are offered, return to [Mailchimp to modify the template](https://mailchimp.com/developer/transactional/docs/templates-dynamic-content/#editable-content-areas), and be sure to add an
editable content region.


STYLING ISSUES
--------------

To ensure all styles are sent properly, consider enabling the 
`Inline CSS STyles in HTML Emails` in your 
[Mailchimp Transactional -> Settings -> Sending Defaults](https://mandrillapp.com/settings/sending-options). 
[More info from Mailchimp](https://mailchimp.com/developer/transactional/docs/outbound-email/#inline-css).
