<?php

namespace Drupal\modal_page\Service;

use Drupal\modal_page\Entity\ModalInterface;
use Drupal\Core\Language\LanguageManagerInterface;
use Drupal\Core\StringTranslation\StringTranslationTrait;
use Drupal\Component\Serialization\Json;
use Drupal\Core\Session\AccountProxyInterface;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Config\ConfigFactoryInterface;

/**
 * Modal Page Helper Service Class.
 */
class ModalPageHelperService {

  use StringTranslationTrait;

  /**
   * The language manager.
   *
   * @var \Drupal\Core\Language\LanguageManagerInterface
   */
  protected $languageManager;

  /**
   * The user current.
   *
   * @var \Drupal\Core\Session\AccountProxyInterface
   */
  protected $loggedUser;

  /**
   * The entity type manager.
   *
   * @var \Drupal\Core\Entity\EntityTypeManagerInterface
   */
  protected $entityTypeManager;

  /**
   * The config factory.
   *
   * @var \Drupal\Core\Config\ConfigFactoryInterface
   */
  protected $configFactory;

  /**
   * Construct.
   */
  public function __construct(LanguageManagerInterface $languageManager, AccountProxyInterface $loggedUser, EntityTypeManagerInterface $entityManager, ConfigFactoryInterface $config_factory) {
    $this->languageManager = $languageManager;
    $this->loggedUser = $loggedUser;
    $this->entityTypeManager = $entityManager;
    $this->configFactory = $config_factory;
  }

  /**
   * Method to verify if User Has Access on Modal.
   */
  public function verifyIfUserHasAccessOnModal($modal) {

    $modalIsPublic = $this->verifyIfModalIsAvailableForEveryone($modal);

    // If Modal is Public return TRUE.
    if ($modalIsPublic) {
      return TRUE;
    }

    $user = $this->entityTypeManager->getStorage('user')->load($this->loggedUser->id());

    $modalRoles = $modal->getRoles();

    foreach ($modalRoles as $role) {

      $userHasRole = $user->hasRole($role);

      // If user has role return TRUE.
      if ($userHasRole) {
        return TRUE;
      }

    }

    return FALSE;
  }

  /**
   * Method to verify if Modal is available for everyone.
   */
  public function verifyIfModalIsAvailableForEveryone($modal) {

    $roles = $modal->getRoles();

    if (empty(array_filter($roles))) {
      return TRUE;
    }

    return FALSE;
  }

  /**
   * Method to Get Modal Options.
   */
  public function getModalOptions(ModalInterface $modal) {

    $modalOptions = [
      'id'                          => $modal->id(),
      'auto_open'                   => $modal->getAutoOpen(),
      'prevent_default'             => $modal->getPreventDefault(),
      'open_modal_on_element_click' => $modal->getOpenModalOnElementClick(),
    ];

    $modalOptions = Json::encode($modalOptions);

    return $modalOptions;
  }

  /**
   * Method to get Cookie Expiration Time.
   *
   * Try to find custom expiration, then global settings, then arbitrary high
   * number (never show up again).
   */
  public function getCookieExpirationTime($modal) {
    $cookieExpiration = $modal->getEnableCustomCookieExpiration() ? $modal->getCustomCookieExpirationTime() : NULL;
    if (!isset($cookieExpiration)) {
      $settings = $this->configFactory->getEditable('modal_page.settings');
      $cookieExpiration = $settings->get('default_cookie_expiration') ?? 10000;
    }
    return $cookieExpiration;
  }

  /**
   * Method with Entity Presave.
   */
  public function entityPresave(&$entity) {

    // Default value only for Modals.
    if (empty($entity->bundle()) || $entity->bundle() != 'modal') {
      return;
    }

    // Set Default value for Label.
    if ($entity->getLabel() === NULL) {

      // Get Default Label.
      $defaultLabel = (string) $this->t('Modal generated at timestamp: @timestamp@', [
        '@timestamp@' => time(),
      ]);

      // Set Label.
      $entity->setLabel($defaultLabel);

      // Display Title as false.
      $entity->setDisplayTitle(FALSE);
    }

    // Default value for Display Title.
    if ($entity->getDisplayTitle() === NULL) {
      $entity->setDisplayTitle(TRUE);
    }

    // Default value for Modal Type.
    if ($entity->getType() === NULL) {
      $entity->setType('page');
    }

    // Default value for Pages.
    if ($entity->getPages() === NULL) {
      $entity->setPages('');
    }

    // Default value for auto-open.
    if ($entity->getAutoOpen() === NULL) {
      $entity->setAutoOpen(TRUE);
    }

    // Default value for Modal Header.
    if ($entity->getEnableModalHeader() === NULL) {
      $entity->setEnableModalHeader(TRUE);
    }

    // Default value for Button X.
    if ($entity->getDisplayButtonXclose() === NULL) {
      $entity->setDisplayButtonXclose(TRUE);
    }

    // Default value on Top Right Button Label.
    if ($entity->getTopRightButtonLabel() === NULL) {
      $entity->setTopRightButtonLabel('x');
    }

    // Default value for Horizontal Line Header.
    if ($entity->getInsertHorizontalLineHeader() === NULL) {
      $entity->setInsertHorizontalLineHeader(TRUE);
    }

    // Default value for Horizontal line.
    if ($entity->getInsertHorizontalLineFooter() === NULL) {
      $entity->setInsertHorizontalLineFooter(TRUE);
    }

    // Default value for Modal.
    if ($entity->getEnableModalFooter() === NULL) {
      $entity->setEnableModalFooter(TRUE);
    }

    // Default value on Enable Right Button.
    if ($entity->getEnableRightButton() === NULL) {
      $entity->setEnableRightButton(TRUE);
    }

    // Default value OK Label Button.
    if ($entity->getOkLabelButton() === NULL) {
      $entity->setOkLabelButton((string) $this->t('OK'));
    }

    // Default value Show Option.
    if ($entity->getEnableDontShowAgainOption() === NULL) {
      $entity->setEnableDontShowAgainOption(TRUE);
    }

    // Default value for Don't Show Again Label.
    if ($entity->getDontShowAgainLabel() === NULL) {
      $entity->setDontShowAgainLabel((string) $this->t("Don't show again"));
    }

    // Default value Custom Cookie.
    if ($entity->getEnableCustomCookieExpiration() === NULL) {
      $entity->setEnableCustomCookieExpiration(FALSE);
    }

    // Default value Custom Cookie Expiration Time.
    if ($entity->getCustomCookieExpirationTime() === NULL) {
      $entity->setCustomCookieExpirationTime(10000);
    }

    // Default value for Get Modal size.
    if ($entity->getModalSize() === NULL) {
      $entity->setModalSize('modal-md');
    }

    // Default value for Modal Esc Key.
    if ($entity->getCloseModalEscKey() === NULL) {
      $entity->setCloseModalEscKey(TRUE);
    }

    // Default value for Modal Outside.
    if ($entity->getCloseModalClickingOutside() === NULL) {
      $entity->setCloseModalClickingOutside(TRUE);
    }

    // Default value Published.
    if ($entity->getPublished() === NULL) {
      $entity->setPublished(TRUE);
    }

    // Default value for Roles.
    if ($entity->getRoles() === NULL) {
      $entity->setRoles([]);
    }

    // Default value for Languages to Show.
    if ($entity->getLanguagesToShow() === NULL) {
      $entity->setLanguagesToShow([]);
    }

    // Default value for Languages to Show.
    if ($entity->getModalClass() === NULL) {
      $entity->setModalClass('');
    }

    // Default value for Enable Left Button.
    if ($entity->getEnableLeftButton() === NULL) {
      $entity->setEnableLeftButton(FALSE);
    }

    // Default value for Left Label Button.
    if ($entity->getLeftLabelButton() === NULL) {
      $entity->setLeftLabelButton($this->t('Dismiss'));
    }

    // Default value for Enable Redirect Button.
    if ($entity->getEnableRedirectLink() === NULL) {
      $entity->setEnableRedirectLink(FALSE);
    }

    // Default value for Redirect Label Button.
    if ($entity->getRedirectLink() === NULL) {
      $entity->setRedirectLink('');
    }

    // Default value for Modal Video Link.
    if ($entity->getModalVideoLink() === NULL) {
      $entity->setModalVideoLink('');
    }

    // Default value for Height Offset Touch.
    if ($entity->getHeightOffsetTouch() === NULL) {
      $entity->setHeightOffsetTouch('start');
    }
  }

  /**
   * Method to remove cookies from the session.
   */
  public function removeCookie(string $cookie) {
    $cookies = \Drupal::request()->cookies;

    if ($cookies->has($cookie)) {
      setcookie($cookie, TRUE, \Drupal::time()->getRequestTime() - 3600, '/');
      $cookies->remove($cookie);
    }
  }

}
