<?php /**
 * @file
 * Contains \Drupal\serial\Plugin\Field\FieldFormatter\SerialFormatterDefault.
 */

namespace Drupal\serial\Plugin\Field\FieldFormatter;

use Drupal\Core\Field\FormatterBase;

/**
 * @FieldFormatter(
 *  id = "serial_formatter_default",
 *  label = @Translation("Default"),
 *  field_types = {"SERIAL_FIELD_TYPE"}
 * )
 */
class SerialFormatterDefault extends FormatterBase {

  /**
   * @FIXME
   * Move all logic relating to the serial_formatter_default formatter into this
   * class. For more information, see:
   *
   * https://www.drupal.org/node/1805846
   * https://api.drupal.org/api/drupal/core%21lib%21Drupal%21Core%21Field%21FormatterInterface.php/interface/FormatterInterface/8
   * https://api.drupal.org/api/drupal/core%21lib%21Drupal%21Core%21Field%21FormatterBase.php/class/FormatterBase/8
   */

}
