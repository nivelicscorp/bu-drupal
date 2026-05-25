<?php

namespace Drupal\Tests\modal_page\Unit;

use Drupal\Component\Serialization\Json;
use Drupal\Tests\UnitTestCase;
use Drupal\Core\Language\LanguageManagerInterface;
use Drupal\Core\Session\AccountProxyInterface;
use Drupal\Core\Config\Entity\ConfigEntityStorageInterface;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\modal_page\Service\ModalPageHelperService;
use Drupal\Core\Config\ConfigFactoryInterface;

/**
 * Tests Modal Page Helper Service Class.
 *
 * @group modal_page
 */
class ModalPageHelperServiceTest extends UnitTestCase {

  /**
   * The language manager mock.
   *
   * @var \Drupal\Core\Language\LanguageManagerInterface|\PHPUnit\Framework\MockObject\MockObject
   */
  protected $languageManager;

  /**
   * Current logged user mock.
   *
   * @var \Drupal\Core\Session\AccountProxyInterface|\PHPUnit\Framework\MockObject\MockObject
   */
  protected $currentUser;

  /**
   * An array of modals mock.
   *
   * @var \Drupal\modal_page\Entity\Modal|\PHPUnit\Framework\MockObject\MockObject
   */
  protected $modals;

  /**
   * The config entity storage mock.
   *
   * @var \Drupal\Core\Config\Entity\ConfigEntityStorageInterface|\PHPUnit\Framework\MockObject\MockObject
   */
  protected $configEntityStorage;

  /**
   * The entity type manager mock.
   *
   * @var \Drupal\Core\Entity\EntityTypeManagerInterface|\PHPUnit\Framework\MockObject\MockObject
   */
  protected $entityTypeManager;

  /**
   * The ModalPageHelperService instance.
   *
   * @var \Drupal\modal_page\Service\ModalPageHelperService|\PHPUnit\Framework\MockObject\MockObject
   */
  protected $modalPageHelperService;

  /**
   * The config factory mock.
   *
   * @var \Drupal\Core\Config\ConfigFactoryInterface|\PHPUnit\Framework\MockObject\MockObject
   */
  protected $configFactory;

  /**
   * {@inheritdoc}
   */
  public function setUp(): void {

    parent::setUp();
    /* Mocks LanguageManager. */
    $this->languageManager = $this->createMock(LanguageManagerInterface::class);

    /* Mocks AccountProxy. */
    $this->currentUser = $this->createMock(AccountProxyInterface::class);

    /* Mocks EntityTypeManager. */
    $this->modals = [
      $this->createMock('Drupal\modal_page\Entity\Modal'),
      $this->createMock('Drupal\modal_page\Entity\Modal'),
      $this->createMock('Drupal\modal_page\Entity\Modal'),
    ];

    $this->configEntityStorage = $this
      ->createMock(ConfigEntityStorageInterface::class);

    $this->configEntityStorage->expects($this->any())
      ->method('loadMultiple')
      ->will($this->returnValue($this->modals));

    $this->entityTypeManager = $this
      ->createMock(EntityTypeManagerInterface::class);

    $this->entityTypeManager->expects($this->any())
      ->method('getStorage')
      ->will($this->returnValue($this->configEntityStorage));

    $this->configFactory = $this
      ->createMock(ConfigFactoryInterface::class);

    // Constructs a ModalPageHelperService object.
    $this->modalPageHelperService = new ModalPageHelperService(
      $this->languageManager,
      $this->currentUser,
      $this->entityTypeManager,
      $this->configFactory
    );
  }

  /**
   * Tests verifyIfUserHasAccessOnModal() method.
   */
  public function testUserAccessOnModal() {
    // Valid modal as public.
    $this->modals[0]
      ->expects($this->any())
      ->method('getRoles')
      ->will($this->returnValue([]));

    $this->assertTrue($this->modalPageHelperService->verifyIfUserHasAccessOnModal($this->modals[0]));

    // Valid modal with whole process.
    $roles = [
      'administrator' => 'administrator',
    ];

    $user = $this->createMock('\Drupal\user\Entity\User');
    $user->expects($this->any())
      ->method('hasRole')
      ->willReturn(TRUE);

    $this->configEntityStorage->expects($this->any())
      ->method('load')
      ->will($this->returnValue($user));

    $this->modals[1]
      ->expects($this->any())
      ->method('getRoles')
      ->will($this->returnValue($roles));

    $this->assertTrue($this->modalPageHelperService->verifyIfUserHasAccessOnModal($this->modals[1]));
  }

  /**
   * Tests verifyIfUserHasAccessOnModal() for users who don't have access.
   */
  public function testUserWithoutAccessOnModal() {
    $user = $this->createMock('\Drupal\user\Entity\User');
    $user->expects($this->any())
      ->method('hasRole')
      ->willReturn(FALSE);

    $this->configEntityStorage->expects($this->any())
      ->method('load')
      ->will($this->returnValue($user));

    $this->modals[2]
      ->expects($this->any())
      ->method('getRoles')
      ->will($this->returnValue(['administrator' => 'administrator']));

    $this->assertFalse($this->modalPageHelperService->verifyIfUserHasAccessOnModal($this->modals[2]));
  }

  /**
   * Tests verifyIfModalIsAvailableForEveryone() method.
   */
  public function testIfModalIsAvailableForEveryone() {
    // To validate without any role.
    $this->modals[0]
      ->expects($this->once())
      ->method('getRoles')
      ->will($this->returnValue([]));

    $this->assertTrue($this->modalPageHelperService->verifyIfModalIsAvailableForEveryone($this->modals[0]));

    // To validate with some roles.
    $roles = [
      'administrator' => 'administrator',
      'anonymous' => '0',
      'authenticated' => '0',
      'content_editor' => '0',
    ];

    $this->modals[1]
      ->expects($this->once())
      ->method('getRoles')
      ->will($this->returnValue($roles));

    $this->assertFalse($this->modalPageHelperService->verifyIfModalIsAvailableForEveryone($this->modals[1]));
  }

  /**
   * Tests getModalOptions() method.
   */
  public function testGetModalOptions() {
    $this->modals[0]
      ->expects($this->any())
      ->method('id')
      ->will($this->returnValue(1));

    $this->modals[0]
      ->expects($this->any())
      ->method('getAutoOpen')
      ->will($this->returnValue(TRUE));

    $this->modals[0]
      ->expects($this->any())
      ->method('getOpenModalOnElementClick')
      ->will($this->returnValue(TRUE));

    $assertModal = Json::encode([
      'id' => $this->modals[0]->id(),
      'auto_open' => $this->modals[0]->getAutoOpen(),
      'open_modal_on_element_click' => $this->modals[0]->getOpenModalOnElementClick(),
    ]);

    $this->assertTrue($this->modalPageHelperService->getModalOptions($this->modals[0]) == $assertModal);
  }

}
