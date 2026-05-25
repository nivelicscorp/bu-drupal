<?php

namespace Drupal\media_entity_facebook\Form;

use Drupal\Core\Form\ConfigFormBase;
use Drupal\Core\Form\FormStateInterface;

/**
 * Provides a form to configure Facebook credentials.
 */
class FacebookSettingsForm extends ConfigFormBase {

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'media_entity_facebook_settings_form';
  }

  /**
   * {@inheritdoc}
   */
  protected function getEditableConfigNames() {
    return ['media_entity_facebook.settings'];
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    $settings = $this->config('media_entity_facebook.settings');

    $form['credentials'] = [
      '#type' => 'details',
      '#title' => $this->t('Facebook app credentials'),
      '#description' => $this->t("The Media Entity Facebook module requires a Facebook app ID and app secret. This information is required by Facebook when interacting with its API to retrieve the embed data. Create a Facebook app using a Facebook Developer account and enable that app to use the oEmbed API."),
      '#open' => TRUE,
    ];

    $form['credentials']['facebook_app_id'] = [
      '#type' => 'textfield',
      '#title' => $this->t('App ID'),
      '#size' => 40,
      '#maxlength' => 255,
      '#default_value' => $settings->get('facebook_app_id'),
      '#description' => $this->t('The ID of your Facebook App.'),
    ];

    $form['credentials']['facebook_app_secret'] = [
      '#type' => 'textfield',
      '#title' => $this->t('App secret'),
      '#size' => 40,
      '#maxlength' => 255,
      '#default_value' => $settings->get('facebook_app_secret'),
      '#description' => $this->t('The secret of your Facebook App.'),
    ];

    $form['use_embedded_posts'] = [
      '#type' => 'checkbox',
      '#title' => $this->t('Use Embedded Posts'),
      '#default_value' => $settings->get('use_embedded_posts'),
      '#description' => $this->t('By default, the module uses the <a href="@embedded_link" target="_blank">Embedded Posts</a> which can be used for embedding Facebook content without App Review. You can also use the <a href="@oembed_link" target="_blank">Facebook oEmbed API</a> which usage needs an App Review from Facebook. You will need to clear cache after changing this setting.', [
        '@oembed_link' => 'https://developers.facebook.com/docs/plugins/oembed',
        '@embedded_link' => 'https://developers.facebook.com/docs/plugins/embedded-posts',
      ]),
    ];

    return parent::buildForm($form, $form_state);
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    $this->config('media_entity_facebook.settings')
      ->set('facebook_app_id', $form_state->getValue('facebook_app_id'))
      ->set('facebook_app_secret', $form_state->getValue('facebook_app_secret'))
      ->set('use_embedded_posts', $form_state->getValue('use_embedded_posts'))
      ->save();

    parent::submitForm($form, $form_state);
  }

}
