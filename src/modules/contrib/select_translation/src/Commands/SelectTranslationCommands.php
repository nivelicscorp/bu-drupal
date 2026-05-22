<?php

namespace Drupal\select_translation\Commands;

use Drush\Commands\DrushCommands;

/**
 * Drush 9+ command class containing select translation drush commands.
 *
 * @package Drupal\select_translation\Commands
 */
class SelectTranslationCommands extends DrushCommands {

  /**
   * Select which translation of a node should be displayed.
   *
   * @param int $nid
   *   The Node ID.
   *
   * @command select_translation:translation
   * @aliases select-translation
   *
   * @option mode
   *   The selection mode, it can be: 'default', 'original', or a comma
   *   separated list of language codes. See the API doc for more details.
   * @usage select_translation:translation --mode default
   *
   */
  public function translation($nid, array $options = ['mode' => NULL]) {
    $node_id = filter_var($nid, FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]]);
    if (!$node_id) {
      throw new \Exception(dt("The 'nid' argument must be an integer >= 1."));
    }

    $mode = $options['mode'];
    if ($mode) {
      $node = select_translation_of_node($node_id, $mode);
    }
    else {
      $node = select_translation_of_node($node_id);
    }

    if (!$node) {
      throw new \Exception(dt("Node with 'nid' = $nid not available."));
    }

    \Drupal::logger('select_translation')->info("Selected translation for node $nid: " . $node->language()->getId());
    return $node;
  }

}
