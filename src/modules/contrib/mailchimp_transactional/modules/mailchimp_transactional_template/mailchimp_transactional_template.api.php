<?php

/**
 * @file
 * Hooks provided by the mailchimp_transactional_template module.
 */

/**
 * @addtogroup hooks
 * @{
 */

use Drupal\mailchimp_transactional_template\Entity\TemplateMapInterface;

/**
 * Alter the template map.
 *
 * @param \Drupal\mailchimp_transactional_template\Entity\TemplateMapInterface $template_map
 *   The template map.
 * @param string $module_key
 *   Module key used when searching for a template mapping.
 * @param string $module
 *   Module name used when searching for a template mapping.
 */
function hook_mailchimp_transactional_template_map_alter(TemplateMapInterface $template_map, string $module_key, string $module): void {
}

/**
 * @} End of "addtogroup hooks".
 */
