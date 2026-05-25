# Modal Page

The Modal project allows you to create Modal using CMS only.

You can place your Modal in specific page and configure if it'll appear when
the end-user open the page (auto-open on page load) or if this Modal will appear
when the user click in specific class or ID on HTML.

For a full description of the module, visit the
[project page](https://www.drupal.org/project/modal_page).

Submit bug reports and feature suggestions, or track changes in the
[issue queue](https://www.drupal.org/project/issues/modal_page).


## Table of contents

- Requirements
- Installation
- Configuration
- Hooks and Modal Programmatically
- Maintainers


## Requirements

No special requirements.


## Installation

Install as you would normally install a contributed Drupal module. For further
information, see
[Installing Drupal Modules](https://www.drupal.org/docs/extending-drupal/installing-drupal-modules).


## Configuration

1. Configure your messages in Administration » Structure » Modal
2. Click in Add Modal
3. Set the Title of modal;
4. Set the Text of modal (Body);
5. Set pages to show the Modal;
6. Select if it'll appear on page load or in element click;
7. Use extra configuration in vertical tab (left side);
8. Save.


## Hooks and Modal Programmatically

1. You can insert your Modal programatically using entityTypeManager like this:

  ```
  $modal = \Drupal::entityTypeManager()->getStorage('modal')->create();

  $modal->setId('modal_id');
  $modal->setLabel('Modal Title');
  $modal->setBody('Modal Content');
  $modal->setPages('/hello');
  $modal->save();
  ```

2. You can change Modals before display with these hooks

  - HOOK_modal_alter(&$modal, $modal_id)

    Example:

    ```
    /**
    * Implements hook_modal_alter().
    */
    function PROJECT_modal_alter(&$modal, $modal_id) {
      $modal->setLabel('Title Updated');
      $modal->setBody('Body Updated');
    }
    ```

  - HOOK_modal_ID_alter(&$modal, $modal_id)

    Example:

    ```
    /**
    * Implements hook_modal_ID_alter().
    */
    function PROJECT_modal_ID_alter(&$modal, $modal_id) {
      $modal->setLabel('New Title');
      $modal->setBody('New Body');
    }
    ```

  - HOOK_modal_submit(&$modal, $modal_id)

    Example:

    ```
    /**
    * Implements hook_modal_submit().
    */
    function PROJECT_modal_submit($modal, $modal_state, $modal_id) {

      // Your AJAX here.
      \Drupal::logger('modal_page')->notice('Modal Submit was triggered');

    }

## Scheduling

Modals can be scheduled to be published or unpublished at specified times.

When you have set up Drupal's standard crontab job, the Modal scheduling will be
processed during each cron run. However, if you would like finer granularity for
scheduling but don't want to run Drupal's cron more often, you may use the Modal
cron handler provided. This is an independent cron job which only runs the
scheduler process and does not execute any cron tasks defined by Drupal core or
any other modules.

Modal's cron is at `/modal-page/cron/{cron-key}`. A sample crontab entry to run
the scheduler every minute might look like:
```
* * * * * wget -q -O /dev/null "https://example.com/modal-page/cron/{cron-key}"
```
or
```
* * * * * curl -s -o /dev/null "https://example.com/modal-page/cron/{cron-key}"
```

The scheduler may also be run with a Drush command:
```
drush modal_page:cron
```

## Maintainers

- Renato Gonçalves - [RenatoG](https://www.drupal.org/user/3326031)
- Thalles Ferreira -[thalles](https://www.drupal.org/user/3589086)
- Paulo Henrique Cota Starling - [paulocs](https://www.drupal.org/user/3640109)
