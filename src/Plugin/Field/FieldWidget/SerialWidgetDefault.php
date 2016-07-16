<?php /**
 * @file
 * Contains \Drupal\serial\Plugin\Field\FieldWidget\SerialWidgetDefault.
 */

namespace Drupal\serial\Plugin\Field\FieldWidget;

use Drupal\Core\Field\WidgetBase;

/**
 * @FieldWidget(
 *  id = "serial_widget_default",
 *  label = @Translation("Hidden (Automatic)"),
 *  field_types = {"SERIAL_FIELD_TYPE"}
 * )
 */
class SerialWidgetDefault extends WidgetBase {

  /**
   * @FIXME
   * Move all logic relating to the serial_widget_default widget into this class.
   * For more information, see:
   *
   * https://www.drupal.org/node/1796000
   * https://api.drupal.org/api/drupal/core%21lib%21Drupal%21Core%21Field%21WidgetInterface.php/interface/WidgetInterface/8
   * https://api.drupal.org/api/drupal/core%21lib%21Drupal%21Core%21Field%21WidgetBase.php/class/WidgetBase/8
   */

}
