<?php
declare(strict_types=1);

namespace Drupal\mandrill;

/**
 * Interface for the Mandrill API.
 */
interface MandrillAPIInterface {
  public function isLibraryInstalled();
  public function getMessages($email);
  public function getTemplates();
  public function getSubAccounts();
  public function getUser();
  public function getTags();
  public function getTag($tag);
  public function getTagTimeSeries($tag);
  public function getTagsAllTimeSeries();
  public function sendTemplate($message, $template_id, $template_content);
  public function send(array $message);
}
