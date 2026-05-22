<?php

declare(strict_types=1);

namespace Drupal\mailchimp_transactional\Form;

use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\Extension\ModuleHandlerInterface;
use Drupal\Core\Form\ConfigFormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Mail\MailManagerInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Drupal\Core\Path\PathValidatorInterface;
use Drupal\Core\Link;
use Drupal\Core\Url;
use Drupal\Core\Render\RendererInterface;
use Drupal\mailchimp_transactional\ServiceInterface;
use Drupal\mailchimp_transactional\APIInterface;

/**
 * Implements an Mailchimp Transactional Admin Settings form.
 */
class AdminSettingsForm extends ConfigFormBase {

  /**
   * The mail system manager.
   *
   * @var \Drupal\Core\Mail\MailManagerInterface
   */
  protected $mailManager;

  /**
   * The path validator.
   *
   * @var \Drupal\Core\Path\PathValidatorInterface
   */
  protected $pathValidator;

  /**
   * The renderer.
   *
   * @var \Drupal\Core\Render\RendererInterface
   */
  protected $renderer;

  /**
   * The module handler.
   *
   * @var \Drupal\Core\Extension\ModuleHandlerInterface
   */
  protected $moduleHandler;

  /**
   * The Mailchimp Transactional service.
   *
   * @var \Drupal\mailchimp_transactional\ServiceInterface
   */
  protected $mailchimpTransactional;

  /**
   * The Mailchimp Transactional API service.
   *
   * @var \Drupal\mailchimp_transactional\APIInterface
   */
  protected $mailchimpTransactionalAPI;

  /**
   * Constructor.
   *
   * @param \Drupal\Core\Config\ConfigFactoryInterface $config_factory
   *   The config factory.
   * @param \Drupal\Core\Mail\MailManagerInterface $mail_manager
   *   The mail system manager.
   * @param \Drupal\Core\Path\PathValidatorInterface $path_validator
   *   The path validator.
   * @param \Drupal\Core\Render\RendererInterface $renderer
   *   The object renderer.
   * @param \Drupal\Core\Extension\ModuleHandlerInterface $moduleHandler
   *   The module handler.
   * @param \Drupal\mailchimp_transactional\APIInterface $mailchimp_transactional_api
   *   The Mailchimp Transactional API service.
   * @param \Drupal\mailchimp_transactional\ServiceInterface $mailchimp_transactional
   *   The Mailchimp Transactional service.
   */
  public function __construct(ConfigFactoryInterface $config_factory, MailManagerInterface $mail_manager, PathValidatorInterface $path_validator, RendererInterface $renderer, ModuleHandlerInterface $moduleHandler, APIInterface $mailchimp_transactional_api, ServiceInterface $mailchimp_transactional) {
    parent::__construct($config_factory);
    $this->mailManager = $mail_manager;
    $this->pathValidator = $path_validator;
    $this->renderer = $renderer;
    $this->moduleHandler = $moduleHandler;
    $this->mailchimpTransactionalAPI = $mailchimp_transactional_api;
    $this->mailchimpTransactional = $mailchimp_transactional;

  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('config.factory'),
      $container->get('plugin.manager.mail'),
      $container->get('path.validator'),
      $container->get('renderer'),
      $container->get('module_handler'),
      $container->get('mailchimp_transactional'),
      $container->get('mailchimp_transactional.service'),
    );
  }

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'mailchimp_transactional_admin_settings';
  }

  /**
   * {@inheritdoc}
   *
   * @throws \Exception
   */
  public function buildForm(array $form, FormStateInterface $form_state): array {
    $config = $this->configFactory()->get('mailchimp_transactional.settings');
    $key = $config->get('mailchimp_transactional_api_key');

    $form['mailchimp_transactional_api_key'] = [
      '#title' => $this->t('Mailchimp Transactional API Key'),
      '#type' => 'textfield',
      '#description' => $this->t('Create or grab your API key from the %link.', ['%link' => Link::fromTextAndUrl($this->t('Mailchimp Transactional settings'), Url::fromUri('https://mandrillapp.com/settings/index'))->toString()]),
      '#default_value' => $key,
      '#required' => TRUE,
    ];

    if ($this->moduleHandler->moduleExists('coi')) {
      $form['mailchimp_transactional_api_key']['#config'] = [
        'key' => 'mailchimp_transactional.settings:mailchimp_transactional_api_key',
        'secret' => TRUE,
      ];
    } elseif ($config->hasOverrides('mailchimp_transactional_api_key')) {
      $form['mailchimp_transactional_api_key']['#disabled'] = TRUE;
      $form['mailchimp_transactional_api_key']['#description'] = $this->t('The API key is overridden, likely in settings.php, and cannot be changed here.');
    }

    if (!$this->mailchimpTransactionalAPI->isLibraryInstalled()) {
      $this->messenger()->addWarning($this->t('The Mailchimp Transactional PHP library is not installed. Please see installation directions in README.md'));
    }
    $hasValidKey = $key && !$this->mailchimpTransactionalAPI->isApiKeyValid($key);
    if ($hasValidKey) {
      $this->messenger()->addWarning($this->t('The provided Mailchimp Transactional API key is invalid'));
    }

    $mail_system_path = Url::fromRoute('mailsystem.settings');
    $usage = [];
    foreach ($this->mailchimpTransactional->getMailSystems() as $system) {
      if ($this->mailConfigurationUsesMailchimpTransactionalMail($system)) {
        $system['sender'] = $this->getPluginLabel($system['sender']);
        $system['formatter'] = $this->getPluginLabel($system['formatter']);
        $usage[] = $system;
      }
    }
    if (!empty($usage)) {
      $usage_array = [
        '#theme' => 'table',
        '#header' => [
          $this->t('Key'),
          $this->t('Sender'),
          $this->t('Formatter'),
        ],
        '#rows' => $usage,
      ];
      $form['mailchimp_transactional_status'] = [
        '#type' => 'markup',
        '#markup' => $this->t(
          'Mailchimp Transactional is currently configured to be used by the 
          following Module Keys. To change these settings or configure 
          additional systems to use Mailchimp Transactional, use 
          <a href=":link">Mail System</a>.<br/><br/>@table',
          [
            ':link' => $mail_system_path->toString(),
            '@table' => $this->renderer->render($usage_array),
          ]),
      ];
    }
    elseif (!$form_state->get('rebuild')) {
      $this->messenger()->addWarning($this->t(
        'PLEASE NOTE: Mailchimp Transactional is not currently configured 
        for use by Drupal. In order to route your email through Mailchimp 
        Transactional, you must configure at least one MailSystemInterface 
        (other than mailchimp_transactional) to use "Mailchimp Transactional
        mailer" in <a href=":link">Mail System</a>, or you will only be able 
        to send Test emails through Mailchimp Transactional.',
        [':link' => $mail_system_path->toString()]
      ));
    }
    $form['email_options'] = [
      '#type' => 'fieldset',
      '#collapsible' => TRUE,
      '#title' => $this->t('Email options'),
    ];
    $form['email_options']['mailchimp_transactional_from'] = [
      '#title' => $this->t('From address'),
      '#type' => 'textfield',
      '#description' => $this->t(
        'The sender email address. If this address has not been verified, 
        messages will be queued and not sent until it is. This address will appear 
        in the "from" field, and any emails sent through Mailchimp Transactional 
        with a "from" address will have that address moved to the Reply-To field.'
      ),
      '#default_value' => $config->get('mailchimp_transactional_from_email'),
    ];
    $form['email_options']['mailchimp_transactional_from_name'] = [
      '#type' => 'textfield',
      '#title' => $this->t('From name'),
      '#default_value' => $config->get('mailchimp_transactional_from_name'),
      '#description' => $this->t('Optionally enter a from name to be used.'),
    ];

    $sub_accounts_options = [];
    if ($hasValidKey) {
      $sub_accounts = $this->mailchimpTransactionalAPI->getSubAccounts();
      if (!empty($sub_accounts)) {
        $sub_accounts_options = ['_none' => '-- Select --'];
        foreach ($sub_accounts as $account) {
          if ($account->status == 'active') {
            $sub_accounts_options[$account->id] = $account->name . ' (' . $account->reputation . ')';
          }
        }
      }
    }

    if ($sub_accounts_options !== []) {
      $form['email_options']['mailchimp_transactional_subaccount'] = [
        '#type' => 'select',
        '#title' => $this->t('Subaccount'),
        '#options' => $sub_accounts_options,
        '#default_value' => $config->get('mailchimp_transactional_subaccount'),
        '#description' => $this->t('Choose a subaccount to send through.'),
      ];
    }
    $formats = filter_formats();
    $options = ['' => $this->t('-- Select --')];
    foreach ($formats as $v => $format) {
      $options[$v] = $format->get('name');
    }
    $form['email_options']['mailchimp_transactional_filter_format'] = [
      '#type' => 'select',
      '#title' => $this->t('Input format'),
      '#description' => $this->t('If selected, the input format to apply to the message body before sending to the Mailchimp Transactional API.'),
      '#options' => $options,
      '#default_value' => [$config->get('mailchimp_transactional_filter_format')],
    ];
    $form['send_options'] = [
      '#title' => $this->t('Send options'),
      '#type' => 'fieldset',
      '#collapsible' => TRUE,
    ];
    $form['send_options']['mailchimp_transactional_track_opens'] = [
      '#title' => $this->t('Track opens'),
      '#type' => 'checkbox',
      '#description' => $this->t('Whether or not to turn on open tracking for messages.'),
      '#default_value' => $config->get('mailchimp_transactional_track_opens'),
    ];
    $form['send_options']['mailchimp_transactional_track_clicks'] = [
      '#title' => $this->t('Track clicks'),
      '#type' => 'checkbox',
      '#description' => $this->t('Whether or not to turn on click tracking for messages.'),
      '#default_value' => $config->get('mailchimp_transactional_track_clicks'),
    ];
    $form['send_options']['mailchimp_transactional_url_strip_qs'] = [
      '#title' => $this->t('Strip query string'),
      '#type' => 'checkbox',
      '#description' => $this->t('Whether or not to strip the query string from URLs when aggregating tracked URL data.'),
      '#default_value' => $config->get('mailchimp_transactional_url_strip_qs'),
    ];
    $form['send_options']['mailchimp_transactional_mail_key_blacklist'] = [
      '#title' => $this->t('Content logging blacklist'),
      '#type' => 'textarea',
      '#description' => $this->t('Comma delimited list of Drupal mail keys to exclude content logging for. CAUTION: Removing the default password reset key may expose a security risk.'),
      '#default_value' => $config->get('mailchimp_transactional_mail_key_blacklist'),
    ];

    $form['send_options']['mailchimp_transactional_log_defaulted_sends'] = [
      '#title' => $this->t('Log sends from module/key pairs that are not registered independently in mailsystem.'),
      '#type' => 'checkbox',
      '#description' => $this->t('If you select Mailchimp Transactional as the site-wide default email sender in %mailsystem and check this box, any messages that are sent through Mailchimp Transactional using module/key pairs that are not specifically registered in mailsystem will cause a message to be written to the system log (type: Mailchimp Transactional, severity: info). Enable this to identify keys and modules for automated emails for which you would like to have more granular control. It is not recommended to leave this box checked for extended periods, as it slows Mailchimp Transactional and can clog your logs.',
        [
          '%mailsystem' => Link::fromTextAndUrl($this->t('Mail System'), $mail_system_path)->toString(),
        ]),
      '#default_value' => $config->get('mailchimp_transactional_log_defaulted_sends'),
    ];

    $form['analytics'] = [
      '#type' => 'fieldset',
      '#collapsible' => TRUE,
      '#title' => $this->t('Google analytics'),
    ];
    $form['analytics']['mailchimp_transactional_analytics_domains'] = [
      '#title' => $this->t('Google analytics domains'),
      '#type' => 'textfield',
      '#description' => $this->t('One or more domains for which any matching URLs will automatically have Google Analytics parameters appended to their query string. Separate each domain with a comma.'),
      '#default_value' => $config->get('mailchimp_transactional_analytics_domains'),
    ];
    $form['analytics']['mailchimp_transactional_analytics_campaign'] = [
      '#title' => $this->t('Google analytics campaign'),
      '#type' => 'textfield',
      '#description' => $this->t("The value to set for the utm_campaign tracking parameter. If this isn't provided the messages from address will be used instead."),
      '#default_value' => $config->get('mailchimp_transactional_analytics_campaign'),
    ];
    $form['asynchronous_options'] = [
      '#title' => $this->t('Asynchronous options'),
      '#type' => 'fieldset',
      '#collapsible' => TRUE,
      '#attributes' => [
        'id' => ['mailchimp-transactional-async-options'],
      ],
    ];
    $form['asynchronous_options']['mailchimp_transactional_process_async'] = [
      '#title' => $this->t('Queue outgoing messages'),
      '#type' => 'checkbox',
      '#description' => $this->t('When set, emails will not be immediately sent. Instead, they will be placed in a queue and sent when cron is triggered.'),
      '#default_value' => $config->get('mailchimp_transactional_process_async'),
    ];
    $form['asynchronous_options']['mailchimp_transactional_batch_log_queued'] = [
      '#title' => $this->t('Log queued emails'),
      '#type' => 'checkbox',
      '#description' => $this->t('Do you want to create a log entry when an email is queued to be sent?'),
      '#default_value' => $config->get('mailchimp_transactional_batch_log_queued'),
      '#states' => [
        'invisible' => [
          ':input[name="mailchimp_transactional_process_async"]' => ['checked' => FALSE],
        ],
      ],
    ];
    $form['asynchronous_options']['mailchimp_transactional_queue_worker_timeout'] = [
      '#title' => $this->t('Queue worker timeout'),
      '#type' => 'textfield',
      '#size' => '12',
      '#description' => $this->t('Number of seconds to spend processing messages during cron. Zero or negative values are not allowed.'),
      '#default_value' => $config->get('mailchimp_transactional_queue_worker_timeout'),
      '#states' => [
        'invisible' => [
          ':input[name="mailchimp_transactional_process_async"]' => ['checked' => FALSE],
        ],
      ],
    ];

    $form['actions']['#type'] = 'actions';
    $form['actions']['submit'] = [
      '#type' => 'submit',
      '#value' => $this->t('Save'),
      '#button_type' => 'primary',
    ];
    return parent::buildForm($form, $form_state);
  }

  /**
   * {@inheritdoc}
   */
  public function validateForm(array &$form, FormStateInterface $form_state) {
    // Don't save the API key if it's overridden.
    $config = $this->configFactory()->get('mailchimp_transactional.settings');
    if ($config->hasOverrides('mailchimp_transactional_api_key')) {
      $form_state->unsetValue('mailchimp_transactional_api_key');
    }
  }

  /**
   * Is a the mail configuration using a Mailchimp Transactional mailer?
   *
   * Checks both formatter and sender, returns TRUE if either use it.
   *
   * @param array $configuration
   *   Must have keys sender and formatter set.
   *
   * @return bool
   *   TRUE if configuration uses, FALSE otherwise.
   */
  private function mailConfigurationUsesMailchimpTransactionalMail(array $configuration) {
    // The sender and formatter is required keys.
    if (!isset($configuration['sender']) || !isset($configuration['formatter'])) {
      return FALSE;
    }
    if ($configuration['sender'] === 'mailchimp_transactional_mail' || $configuration['formatter'] === 'mailchimp_transactional_mail') {
      return TRUE;
    }
    return FALSE;
  }

  /**
   * Get the label for a mail plugin.
   *
   * @param string $plugin_id
   *   The ID of a plugin.
   *
   * @return string|\Drupal\Core\StringTranslation\TranslatableMarkup
   *   The plugin label, or an error message.
   *
   * @throws \Drupal\Component\Plugin\Exception\PluginNotFoundException
   */
  private function getPluginLabel(string $plugin_id) {
    $definition = $this->mailManager->getDefinition($plugin_id);
    return $definition['label'] ?? $this->t('Unknown Plugin (%id)', ['%id' => $plugin_id]);
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    $this->configFactory()->getEditable('mailchimp_transactional.settings')
      ->set('mailchimp_transactional_api_key', $form_state->getValue('mailchimp_transactional_api_key'))
      ->set('mailchimp_transactional_from_email', $form_state->getValue('mailchimp_transactional_from'))
      ->set('mailchimp_transactional_from_name', $form_state->getValue('mailchimp_transactional_from_name'))
      ->set('mailchimp_transactional_subaccount', $form_state->getValue('mailchimp_transactional_subaccount'))
      ->set('mailchimp_transactional_filter_format', $form_state->getValue('mailchimp_transactional_filter_format'))
      ->set('mailchimp_transactional_track_opens', $form_state->getValue('mailchimp_transactional_track_opens'))
      ->set('mailchimp_transactional_track_clicks', $form_state->getValue('mailchimp_transactional_track_clicks'))
      ->set('mailchimp_transactional_url_strip_qs', $form_state->getValue('mailchimp_transactional_url_strip_qs'))
      ->set('mailchimp_transactional_mail_key_blacklist', $form_state->getValue('mailchimp_transactional_mail_key_blacklist'))
      ->set('mailchimp_transactional_log_defaulted_sends', $form_state->getValue('mailchimp_transactional_log_defaulted_sends'))
      ->set('mailchimp_transactional_analytics_domains', $form_state->getValue('mailchimp_transactional_analytics_domains'))
      ->set('mailchimp_transactional_analytics_campaign', $form_state->getValue('mailchimp_transactional_analytics_campaign'))
      ->set('mailchimp_transactional_process_async', $form_state->getValue('mailchimp_transactional_process_async'))
      ->set('mailchimp_transactional_batch_log_queued', $form_state->getValue('mailchimp_transactional_batch_log_queued'))
      ->set('mailchimp_transactional_queue_worker_timeout', $form_state->getValue('mailchimp_transactional_queue_worker_timeout'))
      ->save();
  }

  /**
   * {@inheritdoc}
   */
  protected function getEditableConfigNames() {
    return ['mailchimp_transactional.settings'];
  }

}
