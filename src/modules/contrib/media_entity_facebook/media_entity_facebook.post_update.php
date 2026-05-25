<?php

/**
 * @file
 * Post update functions for Media entity Facebook module.
 */

/**
 * Set defaults for new config settings.
 */
function media_entity_facebook_post_update_set_default_config() {
  $config_factory = \Drupal::configFactory();
  $config_factory->getEditable('media_entity_facebook.settings')
    ->set('facebook_app_id', '')
    ->set('facebook_app_secret', '')
    ->save();
}
