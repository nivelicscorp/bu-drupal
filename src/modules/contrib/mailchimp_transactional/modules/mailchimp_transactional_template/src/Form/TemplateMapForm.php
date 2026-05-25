<?php

declare(strict_types=1);

namespace Drupal\mailchimp_transactional_template\Form;

use Drupal\Core\Entity\EntityForm;
use Drupal\Core\Entity\EntityTypeInterface;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Link;
use Drupal\Core\Routing\RouteBuilderInterface;
use Drupal\Core\Url;
use Drupal\mailchimp_transactional\ApiInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Form controller for the TemplateMap entity edit form.
 *
 * @ingroup mailchimp_transactional_template
 */
class TemplateMapForm extends EntityForm {

  /**
   * The Mailchimp Transactional API.
   *
   * @var \Drupal\mailchimp_transactional\ApiInterface
   */
  protected $mailchimpTransactionalApi;

  /**
   * The route builder.
   *
   * @var \Drupal\Core\Routing\RouteBuilderInterface
   */
  protected $routeBuilder;

  /**
   * Class constructor.
   */
  public function __construct(RouteBuilderInterface $route_builder, ApiInterface $mailchimp_transactional_api) {
    $this->routeBuilder = $route_builder;
    $this->mailchimpTransactionalApi = $mailchimp_transactional_api;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('router.builder'),
      $container->get('mailchimp_transactional')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function form(array $form, FormStateInterface $form_state) {
    $form = parent::form($form, $form_state);

    /** @var \Drupal\mailchimp_transactional_template\Entity\TemplateMap $template_map */
    $template_map = $this->entity;

    $templates = $this->mailchimpTransactionalApi->getTemplates();

    $form['label'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Label'),
      '#default_value' => $template_map->label,
      '#description' => $this->t('The human-readable name of this Mailchimp Transactional Template Map entity.'),
      '#required' => TRUE,
    ];

    $form['id'] = [
      '#type' => 'machine_name',
      '#default_value' => $template_map->id,
      '#maxlength' => EntityTypeInterface::BUNDLE_MAX_LENGTH,
      '#machine_name' => [
        'source' => ['label'],
        'exists' => [$this, 'exists'],
      ],
      '#description' => $this->t('A unique machine-readable name for this Mailchimp Transactional Template Map entity. It must only contain lowercase letters, numbers, and underscores.'),
      '#disabled' => !$template_map->isNew(),
    ];

    $form['map_settings'] = [
      '#type' => 'fieldset',
      '#title' => $this->t('Template Map Settings'),
      '#collapsible' => FALSE,
      '#prefix' => '<div id="template-wrapper">',
      '#suffix' => '</div>',
    ];

    $template_names = [];
    foreach ($templates as $template) {
      $template_names[$template->slug] = $template;
    }
    // Check if the currently configured template still exists.
    if (!empty($template_map->templateId) && !array_key_exists($template_map->templateId, $template_names)) {
      $this->messenger()->addWarning($this->t('The configured Mailchimp Transactional template is no longer available, please select a valid one.'));
    }
    if (!empty($templates)) {
      $options = ['' => $this->t('-- Select --')];
      foreach ($templates as $template) {
        $options[$template->slug] = $template->name;
      }
      $form['map_settings']['template_name'] = [
        '#type' => 'select',
        '#title' => $this->t('Email template'),
        '#description' => $this->t('Select a Mailchimp Transactional template.'),
        '#options' => $options,
        '#default_value' => $template_map->template_name ?? '',
        '#required' => TRUE,
        '#ajax' => [
          'callback' => '::templateCallback',
          'wrapper' => 'template-wrapper',
          'method' => 'replaceWith',
          'effect' => 'fade',
          'progress' => [
            'type' => 'throbber',
            'message' => $this->t('Retrieving template information.'),
          ],
        ],
      ];

      $form_template_name = $form_state->getValue('template_name');

      if (!$form_template_name && isset($template_map->template_name)) {
        $form_template_name = $template_map->template_name;
      }

      if ($form_template_name && isset($template_names[$form_template_name])) {
        $regions = ['' => $this->t('-- Select --')] + $this->parseTemplateRegions($template_names[$form_template_name]->publish_code);
        $form['map_settings']['content_area'] = [
          '#type' => 'select',
          '#title' => $this->t('Template region'),
          '#description' => $this->t('Select the template region to use for email content. <i>Note that you can populate more regions by attaching an array to your message with the index "mailchimp_transactional_template_content", using region names as indexes to the content for that region.'),
          '#options' => $regions,
          '#default_value' => $template_map->content_area ?? '',
          '#states' => [
            'disabled' => [
              ':input[name="only_use_merge_vars"]' => ['checked' => TRUE],
            ],
            'required' => [
              ':input[name="only_use_merge_vars"]' => ['checked' => FALSE],
            ],
          ],
        ];
        $form['map_settings']['only_use_merge_vars'] = [
          '#type' => 'checkbox',
          '#title' => $this->t('Only use merge variables'),
          '#description' => $this->t('When checked, the content from Drupal will not be used. You will need to ensure appropriate placeholder values are present in <code>@codeSnippet</code>.', [
            '@codeSnippet' => '$message[\'params\'][\'mailchimp_transactional\'][\'overrides\'][\'global_merge_vars\']',
          ]),
          '#default_value' => $template_map->only_use_merge_vars ?? FALSE,
        ];
      }
      $usable_keys = mailchimp_transactional_template_usage();
      $module_names = mailchimp_transactional_get_module_key_names();
      $mailchimp_transactional_in_use = FALSE;
      $available_modules = FALSE;
      $mailsystem_options = ['' => $this->t('-- None --')];
      foreach ($usable_keys as $key => $sys) {
        $mailchimp_transactional_in_use = TRUE;
        if ($sys === NULL || (isset($template_map->template_name) && $sys == $template_map->template_name)) {
          $mailsystem_options[$key] = $module_names[$key];
          $available_modules = TRUE;
        }
      }

      if ($mailchimp_transactional_in_use) {
        $form['mailsystem_key'] = [
          '#type' => 'select',
          '#title' => $this->t('Email key'),
          '#description' => $this->t(
            'Select a module and mail key to use this template for outgoing email. Note that if an email has been selected in another Template Mapping, it will not appear in this list. These keys are defined through the %MailSystem interface.',
            ['%MailSystem' => Link::fromTextAndUrl($this->t('MailSystem'), Url::fromRoute('mailsystem.settings'))->toString()]
          ),
          '#options' => $mailsystem_options,
          '#default_value' => $template_map->mailsystem_key ?? '',
        ];
        if (!$available_modules) {
          $this->messenger()->addWarning($this->t('All email-using modules that have been assigned to Mailchimp Transactional are already assigned to other template maps'));
        }
      }

      if (!$mailchimp_transactional_in_use) {
        $this->messenger()->addWarning($this->t('You have not assigned any Modules to use Mailchimp Transactional: to use this template, make sure Mailchimp Transactional is assigned in Mail System.'));
      }
    }
    else {
      $this->messenger()->addWarning($this->t(
        'There are no templates to map. Either the api is down, misconfigured, or you haven\'t created a template yet in <a href="@mandrill-templates">Mailchimp Transactional</a>.',
        ['@mandrill-templates' => 'https://mandrillapp.com/templates']
      ));
    }

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function validateForm(array &$form, FormStateInterface $form_state) {
    parent::validateForm($form, $form_state);

    if (empty($form_state->getValue('content_area')) && !$form_state->getValue('only_use_merge_vars')) {
      $form_state->setErrorByName('content_area', $this->t('You must select a content area.'));
    }
  }

  /**
   * AJAX callback handler for TemplateMapForm.
   */
  public function templateCallback($form, FormStateInterface $form_state) {
    return $form['map_settings'];
  }

  /**
   * {@inheritdoc}
   *
   * @throws \Drupal\Core\Entity\EntityStorageException
   */
  public function save(array $form, FormStateInterface $form_state) {
    /** @var \Drupal\mailchimp_transactional_template\Entity\TemplateMap $template_map */
    $template_map = $this->getEntity();
    $template_map->save();

    $this->routeBuilder->setRebuildNeeded();

    $form_state->setRedirect('mailchimp_transactional_template.admin');
    return $template_map->save();
  }

  /**
   * Tests existence of entity.
   */
  public function exists($id): bool {
    $entity = $this->entityTypeManager->getStorage('mailchimp_transactional_template')->getQuery()
      ->condition('id', $id)
      ->accessCheck(TRUE)
      ->execute();
    return (bool) $entity;
  }

  /**
   * Parses a Mailchimp Transactional template to extract its regions.
   */
  private function parseTemplateRegions($html): array {
    $instances = [];
    $offset = 0;
    $inst = NULL;
    while ($offset = strpos($html, 'mc:edit', $offset)) {
      $start = 1 + strpos($html, '"', $offset);
      $length = strpos($html, '"', $start) - $start;
      $inst = substr($html, $start, $length);
      $instances[$inst] = $inst;
      $offset = $start + $length;
    }
    return $instances;
  }

}
