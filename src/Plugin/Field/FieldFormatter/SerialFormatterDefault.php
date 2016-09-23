<?php

namespace Drupal\serial\Plugin\Field\FieldFormatter;

use Drupal\Core\Field\FormatterBase;
use Drupal\Core\Field\FieldItemListInterface;

/**
 * Plugin implementation of the 'serial_formatter_default' formatter.
 *
 * @FieldFormatter(
 *   id = "serial_formatter_default",
 *   label = @Translation("Default"),
 *   field_types = {
 *     "serial",
 *   },
 * )
 */
class SerialFormatterDefault extends FormatterBase {

  /**
   * {@inheritdoc}
   */
  public function viewElements(FieldItemListInterface $items, $langcode) {
    $element = array();

    foreach ($items as $delta => $item) {
      $elements[$delta] = ['#markup' => $item->value];
    }
    return $element;
  }

}
