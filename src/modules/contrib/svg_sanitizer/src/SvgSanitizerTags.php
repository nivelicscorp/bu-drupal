<?php

namespace Drupal\svg_sanitizer;

use enshrined\svgSanitize\data\AllowedTags;
use enshrined\svgSanitize\data\TagInterface;

/**
 * Defines tags to sanitize.
 */
class SvgSanitizerTags implements TagInterface {

  /**
   * Tags.
   *
   * @var array
   */
  protected static $tags = [];

  /**
   * Returns an array of tags.
   *
   * @return array
   *   Tags.
   */
  public static function getTags(): array {
    $allowed = AllowedTags::getTags();

    foreach (self::$tags as $tag) {
      array_push($allowed, $tag);
    }

    return $allowed;
  }

  /**
   * Sets tags.
   *
   * @param string $tagsAsString
   *   Tags, separated by comma.
   */
  public static function setTags(string $tagsAsString): void {
    self::$tags = array_map('trim', explode(',', $tagsAsString));
  }

}
