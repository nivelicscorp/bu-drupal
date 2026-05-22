<?php

declare(strict_types=1);
namespace Drupal\mailchimp_transactional\Form;

use Drupal\Core\Form\ConfirmFormBase;
use Drupal\Core\Url;
use Drupal\Core\Form\FormStateInterface;
use Drupal\mailchimp_transactional\Plugin\Mail\Mail;
use Drupal\mailchimp_transactional\Plugin\Mail\TestMail;

/**
 * Form controller for the Mailchimp Transactional send test email form.
 *
 * @ingroup mailchimp_transactional
 */
class AdminTestForm extends ConfirmFormBase {

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'mailchimp_transactional_test_email';
  }

  /**
   * {@inheritdoc}
   */
  public function getQuestion() {
    return $this->t('Mailchimp Transactional Test Email');
  }

  /**
   * {@inheritdoc}
   */
  public function getDescription() {
    return $this->t('This action will send a test email through Mailchimp Transactional.');
  }

  /**
   * {@inheritdoc}
   */
  public function getCancelUrl() {
    return new Url('mailchimp_transactional.test');
  }

  /**
   * {@inheritdoc}
   */
  public function getConfirmText() {
    return $this->t('Send test email');
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state): array {
    $form = parent::buildForm($form, $form_state);

    $click_tracking_url = Url::fromUri('https://www.drupal.org/project/mailchimp_transactional');

    $mailchimp_transactional_test_mail = \Drupal::config('mailsystem.settings')->get('defaults')['sender'] == 'mailchimp_transactional_test_mail';

    $form['mailchimp_transactional_test_address'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Send to'),
      '#default_value' => \Drupal::config('system.site')->get('mail'),
      '#description' => $this->t('Multiple addresses allowed with comma separation, including <code>name &lt;email@example.com&gt;</code> formatting.'),
      '#required' => TRUE,
    ];

    $form['mailchimp_transactional_test_body'] = [
      '#type' => 'textarea',
      '#title' => $this->t('Message Content'),
      '#default_value' => $this->t(
        'This message was sent from a Drupal site using Mailchimp Transactional to send email. Test click tracking: <a href="@click-tracking-url" title="Test Click Tracking">link</a>.',
        ['@click-tracking-url' => $click_tracking_url->toString()]),
    ];

    // If sending using the mailchimp_transactional_test_mail service,
    // attachments and bcc are not supported.
    if (!$mailchimp_transactional_test_mail) {
      $form['mailchimp_transactional_test_bcc_address'] = [
        '#type' => 'email',
        '#title' => $this->t('Bcc'),
        '#description' => $this->t('An optional address to BCC.'),
      ];

      $form['include_attachment'] = [
        '#title' => $this->t('Include attachment'),
        '#type' => 'checkbox',
        '#description' => $this->t('If checked, the Drupal icon will be included as an attachment.'),
        '#default_value' => TRUE,
      ];
    }

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    $message = [
      'id' => 'mailchimp_transactional_test_email',
      'module' => 'mailchimp_transactional',
      'to' => $form_state->getValue('mailchimp_transactional_test_address'),
      'body' => $form_state->getValue('mailchimp_transactional_test_body'),
      'subject' => $this->t('Drupal Mailchimp Transactional test email'),
    ];

    $bcc_email = $form_state->getValue('mailchimp_transactional_test_bcc_address');

    if (!empty($bcc_email)) {
      $message['bcc_email'] = $bcc_email;
    }

    if ($form_state->getValue('include_attachment')) {
      $message['attachments'][] = \Drupal::service('file_system')->realpath('core/themes/bartik/logo.svg');
      $message['body'] .= ' ' . $this->t('The Drupal icon is included as an attachment to test the attachment functionality.');
    }

    // Get Mailchimp Transactional mailer service from Mailsystem settings.
    // This service will either be mailchimp_transactional_mail or
    // mailchimp_transactional_test_mail or the route that exposes this form
    // won't even show up - see MailerAccessCheck.php.
    $sender = \Drupal::config('mailsystem.settings')->get('defaults')['sender'];
    if ($sender == 'mailchimp_transactional_mail') {
      /** @var \Drupal\mailchimp_transactional\Plugin\Mail\Mail $mailchimp_transactional */
      $mailer = new Mail();
    }
    elseif ($sender == 'mailchimp_transactional_test_mail') {
      /** @var \Drupal\mailchimp_transactional\Plugin\Mail\TestMail $mailchimp_transactional */
      $mailer = new TestMail();
    }

    // Ensure we have a mailer and send the message.
    if (isset($mailer) && $mailer->mail($message)) {
      $this->messenger()->addStatus($this->t('Test email has been sent.'));
    }
  }

}
