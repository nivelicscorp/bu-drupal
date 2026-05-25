<?php

declare(strict_types=1);

namespace Drupal\modal_page\Service;

use Drupal\Component\Datetime\TimeInterface;
use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Logger\LoggerChannelFactoryInterface;

/**
 * Class for service to handle Modal page scheduling.
 */
final class ModalPageScheduler {

  /**
   * Constructs a ModalPageScheduler object.
   */
  public function __construct(
    private readonly EntityTypeManagerInterface $entityTypeManager,
    private readonly ConfigFactoryInterface $configFactory,
    private readonly LoggerChannelFactoryInterface $loggerFactory,
    private readonly TimeInterface $datetime,
  ) {}

  /**
   * Process scheduling of Modal.
   */
  public function processScheduling(): void {

    $flush_cache = FALSE;
    $now = $this->datetime->getRequestTime();

    $modals = $this->entityTypeManager->getStorage('modal')->loadMultiple();
    $logger = $this->loggerFactory->get('modal_page');

    /** @var \Drupal\modal_page\Entity\Modal $modal */
    foreach ($modals as $modal) {

      $save = FALSE;

      if ($publish_on = $modal->getPublishOn()) {
        if ($now >= $publish_on) {

          if (!$modal->getPublished()) {
            $flush_cache = TRUE;
            $modal->setPublished(TRUE);
            $logger->notice('Modal @label published via scheduling.', ['@label' => $modal->label()]);
          }
          // Remove the value.
          $modal->setPublishOn(0);
          $save = TRUE;
        }
      }

      if ($unpublish_on = $modal->getUnpublishOn()) {
        if ($now >= $unpublish_on) {
          if ($modal->getPublished()) {
            $flush_cache = TRUE;
            $modal->setPublished(FALSE);
            $logger->notice('Modal @label unpublished via scheduling.', ['@label' => $modal->label()]);
          }
          // Remove the value.
          $modal->setUnpublishOn(0);
          $save = TRUE;
        }
      }

      if ($save) {
        $modal->save();
      }

    }

    if ($flush_cache && $this->configFactory->get('modal_page.settings')->get('clear_caches_on_modal_save')) {
      // A modal was changed. Flush the cache.
      drupal_flush_all_caches();
    }

  }

}
