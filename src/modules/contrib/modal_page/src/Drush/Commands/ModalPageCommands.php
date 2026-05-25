<?php

namespace Drupal\modal_page\Drush\Commands;

use Drupal\Core\Utility\Token;
use Drupal\modal_page\Service\ModalPageScheduler;
use Drush\Attributes as CLI;
use Drush\Commands\DrushCommands;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * A Drush commandfile.
 */
final class ModalPageCommands extends DrushCommands {

  /**
   * Constructs a ModalPageCommands object.
   */
  public function __construct(
    private readonly Token $token,
    private readonly ModalPageScheduler $modalPageScheduler,
  ) {
    parent::__construct();
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('token'),
      $container->get('modal_page.scheduler'),
    );
  }

  // Run Modal cron scheduling.
  #[CLI\Command(name: 'modal_page:cron', aliases: ['modal-page-cron'])]
  #[CLI\Usage(name: 'modal_page:cron modal-page-cron', description: 'Run Modal Page cron scheduling')]

  /**
   * Cron method.
   */
  // @todo Fix the order here. This method can be after the "create"
  // However is necessary to validate to make sure that still working.
  // phpcs:ignore
  public function cron(): void {
    $this->modalPageScheduler->processScheduling();
  }

}
