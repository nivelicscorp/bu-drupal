<?php

namespace Drupal\bu_components\Controller;

class Components {
  function test_page() {
    return array(
      'collapsible_block' => array(
        '#prefix' => '<h1>***This is a collapsible block***</h1>',
        '#theme' => 'bu_collapsible_block',
        '#title' => 'Fishes in New Zeland',
        '#items' => array(
          'kilometers',
          'many years ago',
          'spider',
          'black stuff',
          'black stuff',
          'access',
          'permission to investigate',
        ),
      ),
      'square_button_arrow' => array(
        '#prefix' => '<h1>***This is a square button***</h1>',
        '#theme' => 'bu_square_button_arrow',
        '#link_url' => '/',
        '#link_text' => 'Nuestros Abogados',
        '#text_above' => 'Conozca',
        '#text_below' => 'Lorem ipsum dolor sit amet, consectetur adipiscing elit',
        '#color' => 'blue',
      ),
      'rounded_button_arrow' => array(
        '#prefix' => '<h1>***This is a rounded button***</h1>',
        '#theme' => 'bu_expanded_rounded_button_arrow',
        '#link_url' => '/',
        '#link_text' => 'See the magic',
        '#plus_sign' => TRUE,
      ),
      'outstanding_items' => array(
        '#prefix' => '<h1>***This is the outstanding items block (Últimas Noticias, Boletines Relacionados, etc)***</h1>',
        '#theme' => 'bu_outstanding_items',
        '#title' => 'Outstanding News',
        '#items' => array(
          array (
            'category' => 'Infrastructure and Public Services',
            'text' => 'Lorem ipsum dolor sit amet, consectetur adipiscing elit. Integer nec odio. Praesent libero. Sed cursus ante dapibus diam',
          ),
          array (
            'category' => 'Competition and Integrations',
            'text' => 'Lorem ipsum dolor sit amet, consectetur adipiscing elit. Integer nec odio. Praesent libero. Sed cursus ante dapibus diam',
          ),
          array (
            'category' => 'Banking and Financial Services',
            'text' => 'Lorem ipsum dolor sit amet, consectetur adipiscing elit. Integer nec odio. Praesent libero. Sed cursus ante dapibus diam',
          ),
        ),
        '#bottom_link_url' => '/node/1',
        '#bottom_link_text' => 'See all News',
        '#title_color' => 'blue',
        '#is_floating' => TRUE,
      ),
    );
  }
}
