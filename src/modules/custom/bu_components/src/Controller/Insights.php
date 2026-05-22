<?php

namespace Drupal\bu_components\Controller;

class Insights {
  function insightsPage() {
    return array(
      'page_insights' => array(
        '#theme' => 'bu_insights_page',
        '#title' => 'Insights',
      )
    );
  }
}
