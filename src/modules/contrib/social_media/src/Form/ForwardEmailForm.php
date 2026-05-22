<?php

namespace Drupal\social_media\Form;

use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * The class to forward emails.
 */
class ForwardEmailForm extends FormBase {

  /**
   * The config Factory.
   *
   * @var \Drupal\Core\Config\ConfigFactoryInterface
   */
  protected $configFactory;

  /**
   * The request object.
   *
   * @var \Symfony\Component\HttpFoundation\RequestStack
   */
  protected $requestStack;

  /**
   * The mail manager.
   *
   * @var \Drupal\Core\Mail\MailManagerInterface
   */
  protected $mailManager;

  /**
   * The language manager.
   *
   * @var \Drupal\Core\Language\LanguageManagerInterface
   */
  protected $languageManager;

  /**
   * A logger instance.
   *
   * @var \Psr\Log\LoggerInterface
   */
  protected $logger;

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    $instance = parent::create($container);
    $instance->configFactory = $container->get('config.factory');
    $instance->requestStack = $container->get('request_stack');
    $instance->mailManager = $container->get('plugin.manager.mail');
    $instance->languageManager = $container->get('language_manager');
    $instance->logger = $container->get('logger.factory')->get('action');
    return $instance;
  }

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'forward_email_form';
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {

    $form['email'] = [
      '#type' => 'email',
      '#title' => $this->t('Email address'),
      '#required' => TRUE,
      '#description' => $this->t('The person email address whom you want to send'),
    ];

    $form['subject'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Subject'),
      '#required' => TRUE,
      '#default_value' => $this->requestStack->getCurrentRequest()
        ->get('subject'),
    ];

    $form['body'] = [
      '#type' => 'textarea',
      '#title' => $this->t('Body'),
      '#required' => TRUE,
      '#default_value' => $this->requestStack->getCurrentRequest()->get('body'),
    ];

    $form['submit'] = [
      '#type' => 'submit',
      '#value' => $this->t('Send'),
    ];

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {

    $recipient = $form_state->getValue('email');
    $params['message'] = $form_state->getValue('body');
    $params['subject'] = $form_state->getValue('subject');
    $langcode = $this->languageManager->getCurrentLanguage()->getId();

    $result = $this->mailManager->mail('social_media', 'forward_email', $recipient, $langcode, $params, NULL, TRUE);
    if ($result['result'] !== TRUE) {
      $this->logger->notice('Sent email to %recipient', ['%recipient' => $recipient]);
      $this->messenger()->addError($this->t('There was a problem sending your message and it was not sent.'));
    }
    else {
      $this->logger->notice('Sent email to %recipient', ['%recipient' => $recipient]);
      $this->messenger()->addMessage($this->t('Your message has been send to @email', ['@email' => $recipient]));
    }
  }

}
