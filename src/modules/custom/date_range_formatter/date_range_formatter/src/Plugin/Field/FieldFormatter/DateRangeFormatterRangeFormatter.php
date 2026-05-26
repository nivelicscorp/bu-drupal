<?php

namespace Drupal\date_range_formatter\Plugin\Field\FieldFormatter;

use Drupal\Core\Field\FieldItemListInterface;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\StringTranslation\StringTranslationTrait;
use Drupal\datetime\Plugin\Field\FieldFormatter\DateTimeCustomFormatter;
use Drupal\datetime_range\DateTimeRangeTrait;

/**
 * Plugin implementation of the 'Custom' formatter for 'daterange' fields.
 *
 * This formatter renders the data range as plain text, with a fully
 * configurable date format using the PHP date syntax and separator.
 *
 * @FieldFormatter(
 *   id = "date_range_without_time",
 *   label = @Translation("Date range"),
 *   field_types = {
 *     "daterange"
 *   }
 * )
 */
class DateRangeFormatterRangeFormatter extends DateTimeCustomFormatter {

  use DateTimeRangeTrait;
  use StringTranslationTrait;

  /**
   * {@inheritdoc}
   */
  public static function defaultSettings() {
    return [
      'separator' => '-',
      'one_day' => 'd F Y',
      'one_month' => 'd - {d} F Y',
      'several_months' => 'd F - {d} {F} Y',
      'several_years' => 'd F Y - {d} {F} {Y}',
    ] + parent::defaultSettings();
  }

  /**
   * {@inheritdoc}
   */
  public function viewElements(FieldItemListInterface $items, $langcode) {
    $elements = [];

    foreach ($items as $delta => $item) {
      // To make this module compatible with optional_end_date
      // Deal with start_date and end date separately.
      if (!empty($item->start_date)) {
        /** @var \Drupal\Core\Datetime\DrupalDateTime $start_date */
        $start_date = $item->start_date->getTimestamp();
        // Process optional end date.
        if (!empty($item->end_date)) {
          /** @var \Drupal\Core\Datetime\DrupalDateTime $end_date */
          $end_date = $item->end_date->getTimestamp();
          if ($start_date !== $end_date) {
            $format = $this->getSetting('several_years');
            if (date('Y', $start_date) === date('Y', $end_date)) {
              $format = $this->getSetting('several_months');
            }
            if (date('m.Y', $start_date) === date('m.Y', $end_date)) {
              $format = $this->getSetting('one_month');
            }
            if (date('d.m.Y', $start_date) === date('d.m.Y', $end_date)) {
              $format = $this->getSetting('one_day');
            }
            $date_str = \Drupal::service('date.formatter')->format($start_date, 'custom', preg_replace('/\{([a-zA-Z])\}/', '{\\\$1}', $this->t($format)));
            $matches = array();
            if (preg_match_all('/\{([a-zA-Z])\}/', $date_str, $matches)) {
              foreach ($matches[1] as $match) {
                $date_str = preg_replace('/\{' . $match . '\}/', \Drupal::service('date.formatter')->format($end_date, 'custom', $match), $date_str);
              }
            }
            $elements[$delta] = ['#markup' => '<span class="date-display-range">' . $date_str . '</span>',];
          }
        }
        if (!array_key_exists($delta, $elements)) {
          // No end date provided or end date equals start date use single formatting.
          $single_format = $this->getSetting('single');
          $elements[$delta] = ['#markup' => \Drupal::service('date.formatter')->format($start_date, 'custom', $this->t($single_format))];
        }
      }
    }
    return $elements;
  }

  /**
   * {@inheritdoc}
   */
  public function settingsForm(array $form, FormStateInterface $form_state) {
    $form = parent::settingsForm($form, $form_state);
    unset($form['date_format']);
    $form['single'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Date format for single date'),
      '#default_value' => $this->getSetting('single') ? : 'd F Y',
    ];
    $form['single_all_day'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Date format for the single date if the date is "all day"'),
      '#default_value' => $this->getSetting('single_all_day') ? : 'd F Y',
    ];
    $form['one_day'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Date format for the single day date range'),
      '#default_value' => $this->getSetting('one_day') ? : 'd F Y',
    ];
    $form['one_month'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Date format for the single month date range'),
      '#default_value' => $this->getSetting('one_month') ? : 'd - {d} F Y',
    ];
    $form['several_months'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Date format for the single year date range'),
      '#default_value' => $this->getSetting('several_months') ? : 'd F - {d} {F} Y',
    ];
    $form['several_years'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Date format for multiple years date range'),
      '#default_value' => $this->getSetting('several_years') ? : 'd F Y - {d} {F} {Y}',
    ];
    $form['help'] = [
      '#type' => 'markup',
      '#markup' => $this->t('A user-defined date format. See the <a href="@url">PHP manual</a> for available options.', ['@url' => 'http://php.net/manual/function.date.php']) .
        '<br />' . $this->t('Use letters in braces for end date elements, for example, {d} means the day of the end date.') .
        '<br />' . $this->t('These format values are translated, for example, t("d F Y") instead of "d F Y" will be used as the actual date format.'),
    ];

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function settingsSummary() {
    $summary = parent::settingsSummary();
    $summary[] = $this->t('Display date range using formats: @one_day, @one_month, @several_months, @several_years',
        array(
          '@one_day' => $this->getSetting('one_day') ? : 'd F Y',
          '@one_month' => $this->getSetting('one_month') ? : 'd - {d} F Y',
          '@several_months' => $this->getSetting('several_months') ? : 'd F - {d} {F} Y',
          '@several_years' => $this->getSetting('several_years') ? : 'd F Y - {d} {F} {Y}',
        )
      );

    return $summary;
  }

}
