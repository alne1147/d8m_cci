<?php

/**
 * @file
 * Definition of \Drupal\editor_experience\Plugin\CKEditorPlugin\BtButton.
 */

namespace Drupal\editor_experience\Plugin\CKEditorPlugin;

use Drupal\ckeditor\CKEditorPluginBase;
use Drupal\editor\Entity\Editor;

/**
 * Defines the "Bootstrap Buttons" plugin.
 *
 * @CKEditorPlugin(
 *   id = "btbutton",
 *   label = @Translation("Bootstrap Buttons")
 * )
 */
class BtButton extends CKEditorPluginBase {

  /**
 * Implements \Drupal\ckeditor\Plugin\CKEditorPluginInterface::isInternal().
 */
public function isInternal() {
  return FALSE;
}

/**
 * Implements \Drupal\ckeditor\Plugin\CKEditorPluginInterface::getFile().
 */
public function getFile() {
  return libraries_get_path('btbutton') . '/plugin.js';
}
/**
   * Implements \Drupal\ckeditor\Plugin\CKEditorPluginButtonsInterface::getButtons().
   */
  public function getButtons() {
    return [
      'btbutton' => [
        'label' => t('Bootstrap Buttons'),
        'image' => libraries_get_path('btbutton') . '/icons/btbutton.png'
      ]
    ];
  }
}
