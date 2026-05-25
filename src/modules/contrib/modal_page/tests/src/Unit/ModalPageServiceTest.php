<?php

namespace Drupal\Tests\modal_page\Unit;

use Drupal\Tests\UnitTestCase;
use Drupal\modal_page\Service\ModalPageService;
use Drupal\Core\Language\LanguageManagerInterface;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\Path\PathMatcherInterface;
use Drupal\Component\Uuid\UuidInterface;
use Drupal\Core\Config\Entity\ConfigEntityStorageInterface;
use Drupal\Core\Database\Connection;
use Drupal\Core\Session\AccountProxyInterface;
use Drupal\path_alias\AliasManagerInterface;
use Drupal\Core\Extension\ModuleHandlerInterface;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpFoundation\Request;
use Drupal\Core\Path\CurrentPathStack;
use Drupal\user\Entity\User;

/**
 * Tests Modal Page Service Class.
 *
 * @group modal_page
 */
class ModalPageServiceTest extends UnitTestCase {

  /**
   * The ModalPageService instance.
   *
   * @var \Drupal\modal_page\Service\ModalPageService
   */
  protected $modalPageService;

  /**
   * The language manager service.
   *
   * @var \Drupal\Core\Language\LanguageManagerInterface|\PHPUnit\Framework\MockObject\MockObject
   */
  protected $languageManager;

  /**
   * The mocked modals array.
   *
   * @var \Drupal\modal_page\Entity\Modal
   */
  protected $modals;

  /**
   * The search page storage.
   *
   * @var \Drupal\Core\Config\Entity\ConfigEntityStorageInterface|\PHPUnit\Framework\MockObject\MockObject
   */
  protected $configEntityStorage;

  /**
   * The language manager service.
   *
   * @var \Drupal\Core\Entity\EntityTypeManagerInterface|\PHPUnit\Framework\MockObject\MockObject
   */
  protected $entityManager;

  /**
   * The config factory.
   *
   * @var \Drupal\Core\Config\ConfigFactoryInterface|\PHPUnit\Framework\MockObject\MockObject
   */
  protected $configFactory;

  /**
   * Mock database connection.
   *
   * @var \Drupal\Core\Database\Connection
   */
  protected $database;

  /**
   * The request stack.
   *
   * @var \Symfony\Component\HttpFoundation\RequestStack
   */
  protected $requestStack;

  /**
   * The mocked path matcher service.
   *
   * @var \Drupal\Core\Path\PathMatcherInterface|\PHPUnit\Framework\MockObject\MockObject
   */
  protected $pathMatcher;

  /**
   * The UUID generator used for testing.
   *
   * @var \Drupal\Component\Uuid\UuidInterface|\PHPUnit\Framework\MockObject\MockObject
   */
  protected $uuidService;

  /**
   * Current user proxy mock.
   *
   * @var \Drupal\Core\Session\AccountProxyInterface|\PHPUnit\Framework\MockObject\MockObject
   */
  protected $currentUser;

  /**
   * The mocked alias manager.
   *
   * @var \Drupal\path_alias\AliasManagerInterface|\PHPUnit\Framework\MockObject\MockObject
   */
  protected $aliasManager;

  /**
   * The mocked project handler.
   *
   * @var \Drupal\Core\Extension\ModuleHandlerInterface|\PHPUnit\Framework\MockObject\MockObject
   */
  protected $projectHandler;

  /**
   * The mocked current path.
   *
   * @var \Drupal\Core\Path\CurrentPathStack|\PHPUnit\Framework\MockObject\MockObject
   */
  protected $currentPath;

  /**
   * {@inheritdoc}
   */
  public function setUp(): void {
    parent::setUp();
    $this->languageManager = $this->createMock(LanguageManagerInterface::class);

    $this->modals = [
      $this->createMock('Drupal\modal_page\Entity\Modal'),
    ];

    $this->configEntityStorage = $this
      ->createMock(ConfigEntityStorageInterface::class);

    $this->configEntityStorage->expects($this->any())
      ->method('loadMultiple')
      ->will($this->returnValue($this->modals));

    $this->entityManager = $this
      ->createMock(EntityTypeManagerInterface::class);

    $this->entityManager->expects($this->any())
      ->method('getStorage')
      ->will($this->returnValue($this->configEntityStorage));

    $this->configFactory = $this->createMock(ConfigFactoryInterface::class);
    $this->database = $this->createMock(Connection::class);

    $this->requestStack = new RequestStack();
    $this->requestStack->push(new Request());

    $this->pathMatcher = $this->createMock(PathMatcherInterface::class);
    $this->uuidService = $this->createMock(UuidInterface::class);
    $this->currentUser = $this->createMock(AccountProxyInterface::class);
    $this->aliasManager = $this->createMock(AliasManagerInterface::class);
    $this->projectHandler = $this->createMock(ModuleHandlerInterface::class);

    $this->currentPath = $this->createMock(CurrentPathStack::class);

    $this->modalPageService = new ModalPageService(
      $this->languageManager,
      $this->entityManager,
      $this->configFactory,
      $this->database,
      $this->requestStack,
      $this->pathMatcher,
      $this->uuidService,
      $this->currentUser,
      $this->aliasManager,
      $this->projectHandler,
      $this->currentPath,
    );
  }

  /**
   * Tests loadModalsToShow() when modals are unpublished.
   */
  public function testIfModalIsUnpublished() {
    $this->modals[0]
      ->expects($this->once())
      ->method('getPublished')
      ->will($this->returnValue(NULL));

    $this->assertCount(0, $this->modalPageService->loadModalsToShow());
  }

  /**
   * Tests loadModalsToShow() when getType returns NULL.
   */
  public function testIfModalHasNoType() {
    $this->modals[0]
      ->expects($this->once())
      ->method('getPublished')
      ->will($this->returnValue(TRUE));

    $this->modals[0]
      ->expects($this->once())
      ->method('getType')
      ->will($this->returnValue(NULL));

    $this->assertCount(0, $this->modalPageService->loadModalsToShow());
  }

  /**
   * Tests loadModalsToShow() when getType returns page.
   */
  public function testLoadModalsToShowPage() {
    $user = $this->createMock(User::class);
    $user
      ->expects($this->any())
      ->method('hasRole')
      ->willReturn(TRUE);

    $this->configEntityStorage
      ->expects($this->any())
      ->method('load')
      ->will($this->returnValue($user));

    $this->modals[0]
      ->expects($this->any())
      ->method('getPublished')
      ->will($this->returnValue(TRUE));

    $this->modals[0]
      ->expects($this->any())
      ->method('getType')
      ->will($this->returnValue('page'));

    $this->modals[0]
      ->expects($this->any())
      ->method('getPages')
      ->will($this->returnValue(''));

    $this->modals[0]
      ->expects($this->any())
      ->method('getRoles')
      ->will($this->returnValue([
        'administrator' => 'administrator',
      ]));

    $this->modals[0]
      ->expects($this->once())
      ->method('getLanguagesToShow')
      ->will($this->returnValue([]));

    $this->assertCount(1, $this->modalPageService->loadModalsToShow());
  }

  /**
   * Tests loadModalsToShow() with multiple modals having different attributes.
   */
  public function testLoadModalsToShowWithMultipleModals() {
    $this->modals = [
      $this->createMock('Drupal\modal_page\Entity\Modal'),
      $this->createMock('Drupal\modal_page\Entity\Modal'),
      $this->createMock('Drupal\modal_page\Entity\Modal'),
      $this->createMock('Drupal\modal_page\Entity\Modal'),
      $this->createMock('Drupal\modal_page\Entity\Modal'),
      $this->createMock('Drupal\modal_page\Entity\Modal'),
      $this->createMock('Drupal\modal_page\Entity\Modal'),
    ];

    // Set up 4 modals with NULL type.
    // loadModalsToShow() should returns 0 in this case.
    for ($i = 0; $i < 4; $i++) {
      $this->modals[$i]
        ->expects($this->any())
        ->method('getPublished')
        ->will($this->returnValue(TRUE));

      $this->modals[$i]
        ->expects($this->any())
        ->method('getType')
        ->will($this->returnValue(NULL));
    }

    // Set up 2 modals with 'page' type.
    // loadModalsToShow() should returns 2 in this case.
    for ($i = 4; $i < 6; $i++) {
      $this->modals[$i]
        ->expects($this->any())
        ->method('getPublished')
        ->will($this->returnValue(TRUE));

      $this->modals[$i]
        ->expects($this->any())
        ->method('getType')
        ->will($this->returnValue('page'));

      $this->modals[$i]
        ->expects($this->any())
        ->method('getPages')
        ->will($this->returnValue(''));

      $this->modals[$i]
        ->expects($this->any())
        ->method('getRoles')
        ->will($this->returnValue([]));

      $this->modals[$i]
        ->expects($this->once())
        ->method('getLanguagesToShow')
        ->will($this->returnValue([]));
    }

    // Set up 1 modal as unpublished.
    // loadModalsToShow() should keep returning 2.
    $this->modals[5]
      ->expects($this->any())
      ->method('getPublished')
      ->will($this->returnValue(FALSE));

    $this->configEntityStorage = $this
      ->createMock(ConfigEntityStorageInterface::class);

    $this->configEntityStorage->expects($this->any())
      ->method('loadMultiple')
      ->will($this->returnValue($this->modals));

    $this->entityManager = $this
      ->createMock(EntityTypeManagerInterface::class);

    $this->entityManager->expects($this->any())
      ->method('getStorage')
      ->will($this->returnValue($this->configEntityStorage));

    $this->modalPageService = new ModalPageService(
      $this->languageManager,
      $this->entityManager,
      $this->configFactory,
      $this->database,
      $this->requestStack,
      $this->pathMatcher,
      $this->uuidService,
      $this->currentUser,
      $this->aliasManager,
      $this->projectHandler,
      $this->currentPath,
    );

    $this->assertCount(2, $this->modalPageService->loadModalsToShow());
  }

  /**
   * Tests getModalsToShow() when there are no modals created/published.
   */
  public function testGetModalsToShowWithoutModals() {
    $this->modals = [];

    $this->configEntityStorage = $this
      ->createMock(ConfigEntityStorageInterface::class);

    $this->configEntityStorage->expects($this->any())
      ->method('loadMultiple')
      ->will($this->returnValue($this->modals));

    $this->entityManager = $this
      ->createMock(EntityTypeManagerInterface::class);

    $this->entityManager->expects($this->any())
      ->method('getStorage')
      ->will($this->returnValue($this->configEntityStorage));

    $this->modalPageService = new ModalPageService(
      $this->languageManager,
      $this->entityManager,
      $this->configFactory,
      $this->database,
      $this->requestStack,
      $this->pathMatcher,
      $this->uuidService,
      $this->currentUser,
      $this->aliasManager,
      $this->projectHandler,
      $this->currentPath,
    );

    $this->assertFalse($this->modalPageService->getModalsToShow());
  }

  /**
   * Tests getModalsToShow() method.
   */
  public function testGetModalsToShow() {
    $this->modals[0]
      ->expects($this->any())
      ->method('getPublished')
      ->will($this->returnValue(TRUE));

    $this->modals[0]
      ->expects($this->any())
      ->method('getType')
      ->will($this->returnValue('page'));

    $this->modals[0]
      ->expects($this->any())
      ->method('getRoles')
      ->will($this->returnValue([]));

    $this->modals[0]
      ->expects($this->any())
      ->method('getPages')
      ->will($this->returnValue(''));

    $this->modals[0]
      ->expects($this->any())
      ->method('getLanguagesToShow')
      ->will($this->returnValue([]));

    $this->assertCount(1, $this->modalPageService->getModalsToShow());
  }

  /**
   * Tests verifyUserHasPermissionOnModal() method without any permission.
   */
  public function testIfUserHasPermissionOnModalWithoutSetPermissions() {
    $this->modals[0]
      ->expects($this->once())
      ->method('getRoles')
      ->will($this->returnValue([]));

    $this->assertTrue(
      $this
        ->modalPageService
        ->verifyUserHasPermissionOnModal($this->modals[0])
    );
  }

  /**
   * Tests verifyUserHasPermissionOnModal() method with permissions.
   */
  public function testIfUserHasPermissionOnModalWithPermissions() {
    $user = $this->createMock('\Drupal\user\Entity\User');
    $user->expects($this->once())
      ->method('hasRole')
      ->willReturn(TRUE);

    $this->configEntityStorage->expects($this->any())
      ->method('load')
      ->will($this->returnValue($user));

    $this->modals[0]
      ->expects($this->exactly(3))
      ->method('getRoles')
      ->will($this->returnValue([
        'administrator' => 'administrator',
      ]));

    $this->assertTrue(
      $this
        ->modalPageService
        ->verifyUserHasPermissionOnModal($this->modals[0])
    );
  }

  /**
   * Tests getModalToShowByPage() with empty page.
   */
  public function testShowByPageEmpty() {
    // Test without any page.
    $this->modals[0]
      ->expects($this->any())
      ->method('getPages')
      ->will($this->returnValue(''));

    $this->assertEquals(
      $this
        ->modalPageService
        ->getModalToShowByPage($this->modals[0], '<front>'),
      $this->modals[0]
    );
  }

  /**
   * Tests getModalToShowByPage() setting page to front.
   */
  public function testShowByPageSetToFront() {
    // Test with front page.
    $this->modals[0]
      ->expects($this->any())
      ->method('getPages')
      ->will($this->returnValue('<front>'));

    $this->assertEquals(
      $this
        ->modalPageService
        ->getModalToShowByPage($this->modals[0], '<front>'),
      $this->modals[0]
    );
  }

  /**
   * Tests getModalToShowByPage() setting pages to front and /node/1.
   */
  public function testShowByPageWithMultiplePages() {
    $currentPath = '/admin/structure/modal';

    $this->modals[0]
      ->expects($this->any())
      ->method('getPages')
      ->will($this->returnValue('<front>' . PHP_EOL . '/node/1'));

    $this->currentPath
      ->expects($this->any())
      ->method('getPath')
      ->will($this->returnValue($currentPath));

    $this->aliasManager
      ->expects($this->any())
      ->method('getAliasByPath')
      ->withAnyParameters()
      ->will($this->returnValue($currentPath));

    // Assert void function.
    $this->assertNull(
      $this
        ->modalPageService
        ->getModalToShowByPage($this->modals[0], '/admin/structure/modal')
    );
  }

  /**
   * Tests getModalToShowByPage() setting page to node/1.
   */
  public function testShowByPageSetToNode() {
    $node = '/node/1';

    // Test with some node page.
    $this->modals[0]
      ->expects($this->any())
      ->method('getPages')
      ->will($this->returnValue($node));

    $this->currentPath
      ->expects($this->any())
      ->method('getPath')
      ->will($this->returnValue($node));

    $this->aliasManager
      ->expects($this->any())
      ->method('getAliasByPath')
      ->withAnyParameters()
      ->will($this->returnValue($node));

    $this->assertEquals(
      $this
        ->modalPageService
        ->getModalToShowByPage($this->modals[0], $node),
      $this->modals[0]
    );
  }

  /**
   * Tests getCurrentPath() method.
   */
  public function testGetCurrentPath() {
    $this->requestStack->push(Request::create('/admin/structure/modal'));

    $this->modalPageService = new ModalPageService(
      $this->languageManager,
      $this->entityManager,
      $this->configFactory,
      $this->database,
      $this->requestStack,
      $this->pathMatcher,
      $this->uuidService,
      $this->currentUser,
      $this->aliasManager,
      $this->projectHandler,
      $this->currentPath,
    );

    $this->assertEquals(
      '/admin/structure/modal',
      $this->modalPageService->getCurrentPath()
    );
  }

  /**
   * Tests prepareClass() method.
   */
  public function testPrepareClass() {
    // Assert false when empty.
    $this->assertFalse($this->modalPageService->prepareClass(''));

    // Assert true when have some value.
    $class = '.button {}, .button-small {}';

    $this->assertEquals(
      $this->modalPageService->prepareClass($class),
      '.button {} .button-small {}'
    );
  }

  /**
   * Tests verifyModalShouldAppearOnThisLanguage() method.
   */
  public function testIfModalShouldAppearWithoutLanguagesToShow() {
    $this->modals[0]
      ->expects($this->any())
      ->method('getLanguagesToShow')
      ->will($this->returnValue([]));

    $this->assertTrue($this
      ->modalPageService
      ->verifyModalShouldAppearOnThisLanguage($this->modals[0])
    );
  }

}
