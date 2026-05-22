<?php

namespace Drupal\bu_page_header;
use Drupal\Core\Config\ConfigFactoryInterface;

/**
 * Class ValuesHeaderPage.
 */
class ValuesHeaderPage {

  /**
   * Drupal\Core\Config\ConfigFactoryInterface definition.
   *
   * @var \Drupal\Core\Config\ConfigFactoryInterface
   */
  protected $configFactory;
  /**
   * Constructs a new ValuesHeaderPage object.
   */
  public function __construct(ConfigFactoryInterface $config_factory) {
    $this->configFactory = $config_factory;
  }

  public function getValues($path) {
    $info = $this->processInfoPath();
    if (isset($info[$path])) {
      return $info[$path];
    }
    return FALSE;
  }

  public function processInfoPath() {
    $config = $this->configFactory->get('bu_page_header.pageheader')->get('content.fieldset');
    if ($config) {
      foreach ($config as $key => $value) {
        if (is_numeric($key)) {
          $content[$value['url']] = $value;
        }
      }
    }

    return $content;

  }

}
