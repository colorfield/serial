<?php
/**
 * @file
 * Contains \Drupal\serial\Plugin\Field\FieldWidget\SerialWidgetDefault.
 */

namespace Drupal\serial\Plugin\Field\FieldWidget;

use Drupal\Core\Field\FieldItemListInterface;
use Drupal\Core\Field\WidgetBase;
use Drupal\Core\Form\FormStateInterface;

/**
 * Plugin implementation of the 'serial_default' widget.
 *
 * @FieldWidget(
 *   id = "serial_default",
 *   label = @Translation("Hidden (Automatic)"),
 *   field_types = {
 *     "serial"
 *   }
 * )
 */
class SerialWidgetDefault extends WidgetBase {

  /**
   * {@inheritdoc}
   */
  public function formElement(FieldItemListInterface $items, $delta, array $element, array &$form, FormStateInterface $form_state) {
    $element['value'] = array(
      '#type' => 'hidden',
      '#default_value' => isset($items[$delta]->value) ? $items[$delta]->value : NULL,
    );
    return $element;
  }

}
