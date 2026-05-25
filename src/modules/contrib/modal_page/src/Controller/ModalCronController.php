<?php

declare(strict_types=1);

namespace Drupal\modal_page\Controller;

use Drupal\Core\Access\AccessResult;
use Drupal\Core\Controller\ControllerBase;
use Drupal\modal_page\Service\ModalPageScheduler;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\Response;

/**
 * Returns responses for Modal routes.
 */
final class ModalCronController extends ControllerBase {

  /**
   * The controller constructor.
   */
  public function __construct(
    private readonly ModalPageScheduler $modalPageScheduler,
  ) {}

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container): self {
    return new self(
      $container->get('modal_page.scheduler'),
    );
  }

  /**
   * Builds the response.
   */
  public function __invoke() {
    $this->modalPageScheduler->processScheduling();
    return new Response('', Response::HTTP_NO_CONTENT);
  }

  /**
   * Checks access.
   *
   * @param string $cron_key
   *   The cron key.
   *
   * @return \Drupal\Core\Access\AccessResultInterface
   *   The access result.
   */
  public function access($cron_key) {
    $valid_cron_key = $this->state()->get('system.cron_key', '');
    return AccessResult::allowedIf($valid_cron_key == $cron_key);
  }

}
