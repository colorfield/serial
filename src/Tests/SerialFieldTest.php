<?php

namespace Drupal\serial\Tests;

use Drupal\field\Entity\FieldConfig;
use Drupal\simpletest\WebTestBase;
use Drupal\field\Entity\FieldStorageConfig;

/**
 * Tests the creation of serial fields.
 *
 * @group serial
 */
class SerialFieldTest extends WebTestBase {

  /**
   * Modules to enable.
   *
   * @var array
   */
  public static $modules = array(
    'field',
    'node',
    'serial',
  );

  /**
   * A user with permission to create articles.
   *
   * @var \Drupal\user\UserInterface
   */
  protected $webUser;

  /**
   * {@inheritdoc}
   */
  protected function setUp() {
    parent::setUp();

    $this->drupalCreateContentType(array('type' => 'article'));
    $this->webUser = $this->drupalCreateUser(array('create article content', 'edit own article content'));
    $this->drupalLogin($this->webUser);
  }

  /**
   * Helper function for testSerialField().
   */
  public function testSerialField() {

    // Adds the serial field to the article content type.
    FieldStorageConfig::create(array(
      'field_name' => 'field_serial',
      'entity_type' => 'node',
      'type' => 'serial',
    ))->save();
    FieldConfig::create([
      'field_name' => 'field_serial',
      'label' => 'Serial ID',
      'entity_type' => 'node',
      'bundle' => 'article',
    ])->save();

    // @todo review deprecated
    entity_get_form_display('node', 'article', 'default')
      ->setComponent('field_serial', array(
        'type' => 'serial_default_widget',
        'settings' => array(),
      ))
      ->save();

    // @todo review deprecated
    entity_get_display('node', 'article', 'default')
      ->setComponent('field_serial', array(
        'type' => 'serial_default_formatter',
        'weight' => 1,
      ))
      ->save();

    // @todo implement logic from SerialTestCase
    // Display creation form.
    $this->drupalGet('node/add/article');
    $this->assertFieldByName("field_serial[0][value]", '1', 'Widget found.');

    // Test basic entry of serial field.
    $edit = array();

    $this->drupalPostForm('node/add/article', $edit, t('Save'));
    $this->assertRaw('1', 'Serial id ok');
  }

}
