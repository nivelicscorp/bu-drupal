<?php

namespace Drupal\bu_page_header\Form;

use Drupal\Core\Form\ConfigFormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\file\Entity\File;

/**
 * Class PageHeaderForm.
 */
class PageHeaderForm extends ConfigFormBase {

  /**
   * {@inheritdoc}
   */
  protected function getEditableConfigNames() {
    return [
      'bu_page_header.pageheader',
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'page_header_form';
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    $config = $this->config('bu_page_header.pageheader');

    $count = $form_state->get('count');

    // Value for default.
    if ($count === NULL && $config->get('content') === NULL) {
      $count = 1;
      $form_state->set('count', range(1, $count));
      $items = $config->get('content');
    }
    // If has configuration save.
    elseif ($config->get('content.fieldset') && $count == NULL) {
      $value = $config->get('content.fieldset');
      if (is_array($value)) {
        $value = count($value);
        $count = range(1, $value);
      }
      $form_state->set('count', $count);
      $items = $config->get('content');
    }

    $form['content'] = [
      '#type' => 'fieldset',
      '#title' => $this->t('Configuration form'),
      '#group' => 'information',
      '#tree' => TRUE,
    ];

    $form['content']['fieldset'] = [
      '#type' => 'fieldset',
      '#prefix' => '<div id="content-fieldset-wrapper">',
      '#suffix' => '</div>',
    ];

    foreach ($form_state->get('count') as $key => $value) {

      $form['content']['fieldset'][$key] = [
        '#type' => 'fieldset',
        '#title' => $this->t('Configuration'),
      ];

      $form['content']['fieldset'][$key]['url'] = [
        '#type' => 'textfield',
        '#title' => $this->t('Url'),
        '#description' => $this->t('Save the Path'),
        '#maxlength' => 64,
        '#size' => 64,
        '#default_value' => $items['fieldset'][$key]['url'],
      ];

      $form['content']['fieldset'][$key]['title'] = [
        '#type' => 'textfield',
        '#title' => $this->t('Title'),
        '#description' => $this->t('Title page'),
        '#maxlength' => 64,
        '#size' => 64,
        '#default_value' => $items['fieldset'][$key]['title'],
      ];

      $form['content']['fieldset'][$key]['subtitle'] = [
        '#type' => 'textfield',
        '#title' => $this->t('Subtitle'),
        '#description' => $this->t('Subtitle Page'),
        '#maxlength' => 64,
        '#size' => 64,
        '#default_value' => $items['fieldset'][$key]['subtitle'],
      ];

      $form['content']['fieldset'][$key]['lead'] = [
        '#type' => 'textarea',
        '#title' => $this->t('Lead'),
        '#description' => $this->t('Lead Page'),
        '#default_value' => $items['fieldset'][$key]['lead'],
      ];


      // $form['content']['fieldset'][$key]['type'] = [
      //   '#type' => 'select',
      //   '#title' => $this->t('Assset Type'),
      //   '#options' => ['video' => 'video', 'image' => 'image'],
      //   '#default_value' => $items['fieldset'][$key]['type'],
      // ];

      $form['content']['fieldset'][$key]['image'] = [
        '#type' => 'managed_file',
        '#title' => $this->t('Image'),
        '#default_value' => $items['fieldset'][$key]['image'],
        '#upload_location' => 'public://',
        '#upload_validators' => array(
          'file_validate_extensions' => array('jpg png jpeg'),
        ),
      ];

      // $form['content']['fieldset'][$key]['video'] = [
      //   '#type' => 'textfield',
      //   '#title' => $this->t('Video'),
      //   '#default_value' => $items['fieldset'][$key]['video'],
      // ];
    }

    $form['content']['fieldset']['actions'] = [
      '#type' => 'actions',
    ];
    $form['content']['fieldset']['actions']['add'] = [
      '#type' => 'submit',
      '#value' => t('Agregar Establecimiento'),
      '#submit' => ['::addOne'],
      '#ajax' => [
        'callback' => '::addmoreCallback',
        'wrapper' => 'content-fieldset-wrapper',
      ],
    ];

    return parent::buildForm($form, $form_state);
  }

  /**
   * {@inheritdoc}
   */
  public function validateForm(array &$form, FormStateInterface $form_state) {
    parent::validateForm($form, $form_state);
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    parent::submitForm($form, $form_state);

    //dsm($form_state->getValue ('content'));
    $content = $form_state->getValue ('content');
    foreach($content['fieldset'] as $banner){
      $getbgfile = $banner['image'];
      if(is_numeric($getbgfile[0])) {
        $bgfile = File::load($getbgfile[0]);
        $file_usage = \Drupal::service('file.usage');
        if (gettype($bgfile) == 'object') {
          $bgfile->setPermanent();
          $bgfile->save();
          $file_usage->add($bgfile, 'nau', 'file', $getbgfile[0]);
        }
      }
    }


    $this->config('bu_page_header.pageheader')
      ->set('content', $form_state->getValue('content'))
      ->save();
  }

  public function addOne(array &$form, FormStateInterface $form_state) {
    $count = $form_state->get('count');
    $last_item = end($count) + 1;
    array_push($count, $last_item);
    $form_state->set('count', $count);
    $form_state->setRebuild();
  }

  public function addmoreCallback(array &$form, FormStateInterface $form_state) {
    return $form['content']['fieldset'];
  }

}
