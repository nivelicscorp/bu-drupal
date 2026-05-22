<?php

/**
 * @file
 * Contains \Drupal\searcher_modal\Form\SearcherModalForm.
 */

namespace Drupal\searcher_modal\Form;

use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Ajax\AjaxResponse;
use Drupal\Core\Ajax;
use Drupal\Core\Ajax\OpenModalDialogCommand;
use Drupal\Core\Ajax\CssCommand;
use Drupal\Core\Ajax\ReplaceCommand;
use Drupal\views\Views;

/**
 * Class SearcherModalForm.
 *
 * @package Drupal\searcher_modal\Form
 */
class SearcherModalForm extends FormBase {

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'searcher_modal_form';
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    $form['#attached']['library'][] = 'core/drupal.dialog.ajax';
    $form['fulltext_input'] = array(
      '#type' => 'textfield',
      '#attributes' => array('placeholder' => $this->t('Search')),
    );
        
    $form['actions']['#type'] = 'actions';
    $form['actions']['submit'] = array(
      '#type' => 'submit',
      '#value' => $this->t('Search'),
      '#ajax' => array(
        'callback' => '::open_searcher_modal',
        'event' => 'click',
      ),
      
    );
    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {

  }

  public function open_searcher_modal(&$form, FormStateInterface $form_state) {
    $response = new AjaxResponse();
    $fulltext_input = $form_state->getValue('fulltext_input');
    if (!empty($fulltext_input)) {
      $options = array(
        'dialogClass' => 'popup-search',
        'width' => '100%',
        'height' => '100%',
        'drupalAutoButtons' => FALSE,
        'modal' => TRUE,
      );
      $language = \Drupal::languageManager()->getCurrentLanguage()->getId();
      $id_block = "block_1";
      $view = Views::getView('search_everything');
      $view->setDisplay($id_block);
      $view->setExposedInput(array('search_api_fulltext' => $fulltext_input, 'langcode', $language));
      $view->preExecute();
      $view->execute();
      $view_result = $view->buildRenderable($id_block);
      $response->addCommand(new OpenModalDialogCommand('', $view_result, $options));
    }
    else {
      $options = array(
        'dialogClass' => 'popup-search',
        'width' => '100%',
        'height' => '100%',
        'drupalAutoButtons' => FALSE,
        'modal' => TRUE,
      );
      $language = \Drupal::languageManager()->getCurrentLanguage()->getId();
      $id_block = "block_1";
      $view = Views::getView('search_everything');
      $view->setDisplay($id_block);
      $view->setExposedInput(array('search_api_fulltext' => $fulltext_input, 'langcode', $language));
      $view->preExecute();
      $view->execute();
      $view_result = $view->buildRenderable($id_block);
      $response->addCommand(new OpenModalDialogCommand('', $view_result, $options));
      $response->addCommand(new ReplaceCommand('.views-exposed-form h1', t('Search')));
      $response->addCommand(new CssCommand('.views-exposed-form', array('padding-top'=>'30%', 'margin-bottom'=>'30%')));
      $response->addCommand(new ReplaceCommand('.attachment.attachment-after', ''));
      $response->addCommand(new ReplaceCommand('.view-empty', ''));
      $response->addCommand(new Ajax\InvokeCommand('body', 'addClass', array('general-search')));
    }
    return $response;
  }

}
