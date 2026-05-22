MAILCHIMP TRANSACTIONAL ACTIVITY
--------------------------------

View Mailchimp Transactional activity for any entity with a valid email address.
Activity data is fetched from Mailchimp Transactional API, based on the value in
the chosen email address field.


INSTALLATION
------------
This module requires both the base Mailchimp Transactional Module,
as well as the [Entity API module](http://drupal.org/project/entity).


CONFIGURATION
-------------
1. Define which entity types you want to show Mailchimp Transactional activity: 
`Admin » Configuration » Web Services » Mailchimp Transactional`  
2. Choose the Activity Entities tab.
3. Add Activity Entity.
4. Select a Drupal entity type, bundle, and email property. Leave enabled and  
Save.

A Mailchimp Transactional activity local task will appear for any configured 
entity, for example if you chose `User » User » Mail` for your entity property,  
then you'd see an activity report for a user by clicking their username in 
`Admin » People`, then the "Mailchimp Transactional Activity" tab.
