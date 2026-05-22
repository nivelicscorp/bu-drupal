<?php

declare(strict_types=1);
/**
 * @file
 * Contains alteration to transactional service to allow templated emails.
 */

namespace Drupal\mailchimp_transactional_template;

use Drupal\Core\DependencyInjection\ContainerBuilder;
use Drupal\Core\DependencyInjection\ServiceProviderBase;

/**
 * Overrides the Mailchimp Transactional service with the template service.
 *
 * The template services will pass control back to the base service, should
 * a template map not be configured for the current mail action.
 */
class TemplateServiceProvider extends ServiceProviderBase {

  /**
   * {@inheritdoc}
   */
  public function alter(ContainerBuilder $container) {
    $definition = $container->getDefinition('mailchimp_transactional.service');
    $definition->setClass('Drupal\mailchimp_transactional_template\TemplateService');
  }

}
