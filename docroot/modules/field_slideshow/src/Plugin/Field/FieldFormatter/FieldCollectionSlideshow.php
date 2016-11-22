<?php

namespace Drupal\field_slideshow\Plugin\Field\FieldFormatter;

use Drupal\Core\Field\FieldItemListInterface;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Url;
use Drupal\field_collection\Plugin\Field\FieldFormatter\FieldCollectionItemsFormatter;
use Drupal\file\Entity\File;
use Drupal\image\Entity\ImageStyle;

/**
 * Plugin implementation of the 'slideshow' formatter.
 *
 * @FieldFormatter(
 *   id = "field_collection_slideshow",
 *   label = @Translation("Slideshow"),
 *   field_types = {
 *     "field_collection",
 *   }
 * )
 */
class FieldCollectionSlideshow extends FieldCollectionItemsFormatter {

  /**
   * {@inheritdoc}
   */
  public static function defaultSettings() {
    return array(
      'image_field'                         => '',
      'image_style'                         => '',
      'image_link'                          => '',
      'slideshow_colorbox_image_style'      => '',
      'slideshow_colorbox_slideshow'        => '',
      'slideshow_colorbox_slideshow_speed'  => '4000',
      'slideshow_colorbox_transition'       => 'elastic',
      'slideshow_colorbox_speed'            => '350',
      'slideshow_caption'                   => '',
      'slideshow_caption_link'              => '',
      'slideshow_fx'                        => 'fade',
      'slideshow_speed'                     => '1000',
      'slideshow_timeout'                   => '4000',
      'slideshow_order'                     => '',
      'slideshow_controls'                  => 0,
      'slideshow_controls_pause'            => 0,
      'slideshow_controls_position'         => 'after',
      'slideshow_pause'                     => 0,
      'slideshow_start_on_hover'            => 0,
      'slideshow_pager'                     => '',
      'slideshow_pager_position'            => 'after',
      'slideshow_pager_image_style'         => '',
      'slideshow_field_collection_image'    => '',
    ) + parent::defaultSettings();
  }

  /**
   * {@inheritdoc}
   */
  public function settingsForm(array $form, FormStateInterface $form_state) {

    $image_styles = image_style_options(FALSE);
    // Get image_style and image_link form elements from parent method.
    $element = parent::settingsForm($form, $form_state);
    $bundle = $form['#bundle'];

    $definitions = \Drupal::service('entity_field.manager')->getFieldDefinitions('node', $bundle);
    foreach ($definitions as $field_name => $field_definition) {
      if ($field_definition->getType() == 'field_collection') {
        $fname = $field_definition->getName();
      }
    }
    $field_options = $this->fieldSlideshowGetFields('image', $bundle, $fname);
    $element['image_field'] = array(
      '#title'         => t('Image Field'),
      '#type'          => 'select',
      '#default_value' => $this->getSetting('image_field'),
      '#empty_option'  => t('None'),
      '#options'       => $field_options,
      '#required'      => TRUE,
    );
    $element['image_style'] = array(
      '#title'         => t('Image style'),
      '#type'          => 'select',
      '#default_value' => $this->getSetting('image_style'),
      '#empty_option'  => t('None (original image)'),
      '#options'       => $image_styles,
    );
    $link_types = array(
      'content' => t('Content'),
      'file'    => t('File'),
    );
    $element['image_link'] = array(
      '#title'          => t('Links image to'),
      '#type'           => 'select',
      '#default_value'  => $this->getSetting('image_link'),
      '#empty_option'   => t('Nothing'),
      '#options'        => $link_types,
      '#states'         => array(
        'invisible' => array(
          ':input[name$="[settings_edit_form][settings][image_field]"]' => array('value' => ''),
        ),
      ),
    );
    if (\Drupal::moduleHandler()->moduleExists('colorbox')) {
      $element['image_link']['#options']['colorbox'] = 'Colorbox';
      $element['slideshow_colorbox_image_style'] = array(
        '#title'          => t('Colorbox image style'),
        '#type'           => 'select',
        '#default_value'  => $this->getSetting('slideshow_colorbox_image_style'),
        '#empty_option'   => t('None (original image)'),
        '#options'        => image_style_options(FALSE),
        '#states' => array(
          'visible' => array(
            ':input[name$="[settings_edit_form][settings][image_link]"]' => array('value' => 'colorbox'),
          ),
        ),
      );
      $colorbox_slideshow = array(
        'automatic' => t('Automatic'),
        'manual'    => t('Manual'),
      );
      $element['slideshow_colorbox_slideshow'] = array(
        '#title'          => t('Colorbox slideshow'),
        '#type'           => 'select',
        '#default_value'  => $this->getSetting('slideshow_colorbox_slideshow'),
        '#empty_option'   => t('No slideshow'),
        '#options'        => $colorbox_slideshow,
        '#states' => array(
          'visible' => array(
            ':input[name$="[settings_edit_form][settings][image_link]"]' => array('value' => 'colorbox'),
          ),
        ),
      );
      $element['slideshow_colorbox_slideshow_speed'] = array(
        '#title'          => t('Colorbox slideshow speed'),
        '#type'           => 'textfield',
        '#size'           => 5,
        '#default_value'  => $this->getSetting('slideshow_colorbox_slideshow_speed'),
        '#description'    => t('Time between transitions (ms).'),
        '#states' => array(
          'invisible' => array(
            ':input[name$="[settings_edit_form][settings][slideshow_colorbox_slideshow]"]' => array('value' => ''),
          ),
        ),
      );
      $colorbox_transitions = array(
        'none'    => t('None'),
        'elastic' => t('Elastic'),
        'fade'    => t('Fade'),
      );
      $element['slideshow_colorbox_transition'] = array(
        '#title'          => t('Colorbox transition'),
        '#type'           => 'select',
        '#default_value'  => $this->getSetting('slideshow_colorbox_transition'),
        '#options'        => $colorbox_transitions,
        '#states' => array(
          'visible' => array(
            ':input[name$="[settings_edit_form][settings][image_link]"]' => array('value' => 'colorbox'),
          ),
        ),
      );
      $element['slideshow_colorbox_speed'] = array(
        '#title'          => t('Colorbox transition speed'),
        '#type'           => 'textfield',
        '#size'           => 5,
        '#default_value'  => $this->getSetting('slideshow_colorbox_speed'),
        '#description'    => t('Duration of transition (ms).'),
        '#states' => array(
          'visible' => array(
            ':input[name$="[settings_edit_form][settings][image_link]"]' => array('value' => 'colorbox'),
          ),
        ),
      );
    }
    $captions = array(
      'title'   => t('Title text'),
      'alt'     => t('Alt text'),
    );
    $caption_add = $this->fieldSlideshowGetFields('string_long', $bundle, $fname);
    $captions = array_merge($captions, $caption_add);
    $element['slideshow_caption'] = array(
      '#title'          => t('Caption'),
      '#type'           => 'select',
      '#default_value'  => $this->getSetting('slideshow_caption'),
      '#empty_option'   => t('Nothing'),
      '#options'        => $captions,
    );
    $element['slideshow_caption_link'] = array(
      '#title'          => t('Caption link'),
      '#type'           => 'select',
      '#default_value'  => $this->getSetting('slideshow_caption_link'),
      '#empty_option'   => t('Nothing'),
      '#options'        => $link_types,
      '#states' => array(
        'invisible' => array(
          ':input[name$="[settings_edit_form][settings][slideshow_caption]"]' => array('value' => ''),
        ),
      ),
    );
    $element['slideshow_fx'] = array(
      '#title'         => t('Transition effect'),
      '#type'          => 'select',
      '#default_value' => $this->getSetting('slideshow_fx'),
      '#options'       => array(
        'blindX'       => t('blindX'),
        'blindY'       => t('blindY'),
        'blindZ'       => t('blindZ'),
        'cover'        => t('cover'),
        'curtainX'     => t('curtainX'),
        'curtainY'     => t('curtainY'),
        'fade'         => t('fade'),
        'fadeZoom'     => t('fadeZoom'),
        'growX'        => t('growX'),
        'growY'        => t('growY'),
        'scrollUp'     => t('scrollUp'),
        'scrollDown'   => t('scrollDown'),
        'scrollLeft'   => t('scrollLeft'),
        'scrollRight'  => t('scrollRight'),
        'scrollHorz'   => t('scrollHorz'),
        'scrollVert'   => t('scrollVert'),
        'shuffle'      => t('shuffle'),
        'slideX'       => t('slideX'),
        'slideY'       => t('slideY'),
        'toss'         => t('toss'),
        'turnUp'       => t('turnUp'),
        'turnDown'     => t('turnDown'),
        'turnLeft'     => t('turnLeft'),
        'turnRight'    => t('turnRight'),
        'uncover'      => t('uncover'),
        'wipe'         => t('wipe'),
        'zoom'         => t('zoom'),
      ),
    );
    $element['slideshow_speed'] = array(
      '#title'          => t('Transition speed'),
      '#type'           => 'textfield',
      '#size'           => 5,
      '#default_value'  => $this->getSetting('slideshow_speed'),
      '#description'    => t('Duration of transition (ms).'),
      '#required'       => TRUE,
    );
    $element['slideshow_timeout'] = array(
      '#title'          => t('Timeout'),
      '#type'           => 'textfield',
      '#size'           => 5,
      '#default_value'  => $this->getSetting('slideshow_timeout'),
      '#description'    => t('Time between transitions (ms). Enter 0 to disable automatic transitions (then, enable pager and/or controls).'),
      '#required'       => TRUE,
    );
    $element['slideshow_order'] = array(
      '#title'          => t('Order'),
      '#type'           => 'select',
      '#default_value'  => $this->getSetting('slideshow_order'),
      '#empty_option'   => t('Normal'),
      '#options'        => array(
        'reverse' => t('Reverse'),
        'random'  => t('Random'),
      ),
    );
    $element['slideshow_controls'] = array(
      '#title'          => t('Create prev/next controls'),
      '#type'           => 'checkbox',
      '#default_value'  => $this->getSetting('slideshow_controls'),
    );
    $element['slideshow_controls_pause'] = array(
      '#title'          => t('Create play/pause button'),
      '#type'           => 'checkbox',
      '#default_value'  => $this->getSetting('slideshow_controls_pause'),
      '#states' => array(
        'visible' => array(
          ':input[name$="[settings_edit_form][settings][slideshow_controls]"]' => array('checked' => TRUE),
        ),
      ),
    );
    $element['slideshow_controls_position'] = array(
      '#title'          => t('Prev/next controls position'),
      '#type'           => 'select',
      '#options'        => array('before' => t('Before'), 'after' => t('After')),
      '#default_value'  => $this->getSetting('slideshow_controls_position'),
      '#states' => array(
        'visible' => array(
          ':input[name$="[settings_edit_form][settings][slideshow_controls]"]' => array('checked' => TRUE),
        ),
      ),
    );
    $element['slideshow_pause'] = array(
      '#title'          => t('Pause on hover'),
      '#type'           => 'checkbox',
      '#default_value'  => $this->getSetting('slideshow_pause'),
    );
    $element['slideshow_start_on_hover'] = array(
      '#title'          => t('Activate on hover'),
      '#type'           => 'checkbox',
      '#default_value'  => $this->getSetting('slideshow_start_on_hover'),
    );
    $element['slideshow_pager'] = array(
      '#title'          => t('Pager'),
      '#type'           => 'select',
      '#options'        => array('number' => t('Slide number'), 'image' => t('Image')),
      '#empty_option'   => t('None'),
      '#default_value'  => $this->getSetting('slideshow_pager'),
    );
    $element['slideshow_pager_position'] = array(
      '#title'          => t('Pager position'),
      '#type'           => 'select',
      '#options'        => array('before' => t('Before'), 'after' => t('After')),
      '#default_value'  => $this->getSetting('slideshow_pager_position'),
      '#states' => array(
        'invisible' => array(
          ':input[name$="[settings_edit_form][settings][slideshow_pager]"]' => array('value' => ''),
        ),
      ),
    );
    $element['slideshow_pager_image_style'] = array(
      '#title'          => t('Pager image style'),
      '#type'           => 'select',
      '#default_value'  => $this->getSetting('slideshow_pager_image_style'),
      '#empty_option'   => t('None (original image)'),
      '#options'        => image_style_options(FALSE),
      '#states' => array(
        'visible' => array(
          ':input[name$="[settings_edit_form][settings][slideshow_pager]"]' => array('value' => 'image'),
        ),
      ),
    );
    return $element;
  }

  /**
   * {@inheritdoc}
   */
  public function settingsSummary() {

    // Get summary of image_style and image_link from parent method.
    $summary = parent::settingsSummary();

    $image_styles = image_style_options(FALSE);
    // Unset possible 'No defined styles' option.
    unset($image_styles['']);

    // Colorbox!
    $image_link_setting = $this->getSetting('image_link');
    if (isset($image_link_setting) && $image_link_setting == 'colorbox') {
      $link_type_message = t('Link to: @link', array('@link' => $this->getSetting('image_link')));
      $link_type_message .= ' (';
      $colorbox_img_style_settings = $this->getSetting('slideshow_colorbox_image_style');
      if (isset($colorbox_img_style_settings)) {
        $link_type_message .= t('Image style: @style', array('@style' => $image_styles[$this->getSetting('slideshow_colorbox_image_style')]));
      }
      else {
        $link_type_message .= t('Original image');
      }
      $colorbox_slideshow_settings = $this->getSetting('slideshow_colorbox_slideshow');
      if (isset($colorbox_slideshow_settings)) {
        $colorbox_slideshow = array(
          'automatic' => t('Automatic'),
          'manual'    => t('Manual'),
        );
        if (isset($colorbox_slideshow[$this->getSetting('slideshow_colorbox_slideshow')])) {
          $link_type_message .= ', with Slideshow (' . $colorbox_slideshow[$this->getSetting('slideshow_colorbox_slideshow')] . ' - Speed: ' . $this->getSetting('slideshow_colorbox_slideshow_speed') . ')';
        }
      }

      $link_type_message .= ')';
      $summary[] = $link_type_message;
    }

    $caption_types = array(
      'title' => t('Title text'),
      'alt'   => t('Alt text'),
    );

    // Display this setting only if there's a caption.
    $caption_types_settings = $this->getSetting('slideshow_caption');
    if (isset($caption_types[$caption_types_settings])) {
      $caption_message = t('Caption: @caption', array('@caption' => $caption_types[$caption_types_settings]));
      $link_types_settings = $this->getSetting('slideshow_caption_link');
      if (isset($link_types[$link_types_settings])) {
        $caption_message .= ' (' . t('Link to: @link', array('@link' => $link_types[$link_types_settings])) . ')';
      }
      $summary[] = $caption_message;
    }

    $summary[] = t('Transition effect: @effect', array('@effect' => $this->getSetting('slideshow_fx')));
    $summary[] = t('Speed: @speed', array('@speed' => $this->getSetting('slideshow_speed')));
    $summary[] = t('Timeout: @timeout', array('@timeout' => $this->getSetting('slideshow_timeout')));

    $orders = array(
      'reverse' => t('Reverse order'),
      'random'  => t('Random order'),
    );

    $orders_settings = $this->getSetting('slideshow_order');
    if (isset($orders[$orders_settings])) {
      $summary[] = $orders[$orders_settings];
    }

    $pause_button_text = "";
    $slideshow_controls_pause = $this->getSetting('slideshow_controls_pause');
    $slideshow_controls = $this->getSetting('slideshow_controls');
    $slideshow_pause = $this->getSetting('slideshow_pause');
    $slideshow_start_on_hover = $this->getSetting('slideshow_start_on_hover');
    if (isset($slideshow_controls_pause) && $slideshow_controls_pause) {
      $pause_button_text = " " . t("(with play/pause)");
    }
    if (isset($slideshow_controls) && $slideshow_controls) {
      $summary[] = t('Create prev/next controls') . $pause_button_text;
    }
    if (isset($slideshow_pause) && $slideshow_pause) {
      $summary[] = t('Pause on hover');
    }
    if (isset($slideshow_start_on_hover) && $slideshow_start_on_hover) {
      $summary[] = t('Activate on hover');
    }

    switch ($this->getSetting('slideshow_pager')) {
      case 'number':
        $summary[] = t('Pager') . ': ' . t('Slide number');
        break;

      case 'image':
        $pager_image_message = t('Pager') . ': ' . t('Image') . ' (';
        if (!empty($image_styles[$this->getSetting('slideshow_pager_image_style')])) {
          $pager_image_message .= t('Image style: @style', array('@style' => $image_styles[$this->getSetting('slideshow_pager_image_style')]));
        }
        else {
          $pager_image_message .= t('Original image');
        }
        $pager_image_message .= ')';
        $summary[] = $pager_image_message;
        break;
    }
    return $summary;
  }

  /**
   * {@inheritdoc}
   */
  public function viewElements(FieldItemListInterface $items, $langcode) {

    $image_field = $this->getSetting('image_field');
    if ($image_field == '') {
      drupal_set_message(t('Please choose image field in slideshow option in manage display, Now we displayed Field Collections items options.'), 'error');
      return parent::viewElements($items, $langcode);
    }
    $caption = $this->getSetting('slideshow_caption');
    foreach ($items as $key => $item) {
      $render = $item->getFieldCollectionItem()->get($image_field)->first();
      $render_value['#theme'] = 'image_formatter';
      $render_value['#label_display'] = 'hidden';
      $render_value['#url'] = NULL;
      $render_value['#item'] = $render;
      $render_value['#image_style'] = $this->getSetting('image_style');
      $render_value['#item_attributes'] = array();
      $render_item[] = $render_value;
    }
    static $slideshow_count;
    $slideshow_count = (is_int($slideshow_count)) ? $slideshow_count + 1 : 1;

    // Check plugins.
    if (\Drupal::moduleHandler()->moduleExists('libraries')) {
      if (!file_exists(DRUPAL_ROOT . '/libraries/jquery.cycle/jquery.cycle.all.min.js') && !file_exists(DRUPAL_ROOT . '/libraries/jquery.cycle/jquery.cycle.all.js')) {
        $url = Url::fromRoute('system.status');
        drupal_set_message(t('JQuery Cycle must be installed in order to run the slideshow. Please go to !page for instructions.', array('!page' => \Drupal::l(t('Status Report'), $url))), 'warning', FALSE);
      }
    }
    else {
      $url = Url::fromUri('http://drupal.org/project/libraries');
      drupal_set_message(t('Please install the !module module in order to use Field Slideshow.', array('!module' => \Drupal::l('Libraries API', $url))), 'warning', FALSE);
    }

    $elements = array();
    $entity = array();
    $links = array(
      'image_link'              => 'path',
      'slideshow_caption_link'  => 'caption_path',
    );

    // Loop through required links (because image and
    // caption can have different links).
    foreach ($items as $delta => $item) {
      $uri = array();

      // Set Image caption.
      if ($this->getSetting('slideshow_caption') != '') {
        $caption_settings = $this->getSetting('slideshow_caption');
        if ($caption_settings == 'title') {
          $item_settings[$delta]['caption'] = $item->getFieldCollectionItem()->get($image_field)->getValue()[0]['title'];
        }
        elseif ($caption_settings == 'alt') {
          $item_settings[$delta]['caption'] = $item->getFieldCollectionItem()->get($image_field)->getValue()[0]['alt'];
        }
        else {
          $item_settings[$delta]['caption'] = $item->getFieldCollectionItem()->get($caption)->getValue()[0]['value'];
        }
        $item->set('caption', $item_settings[$delta]['caption']);
      }

      // Set Image and Caption Link.
      foreach ($links as $setting => $path) {
        if ($this->getSetting($setting) != '') {
          switch ($this->getSetting($setting)) {
            case 'content':
              $entity = $item->getEntity();
              if (!$entity->isNew()) {
                $uri = $entity->urlInfo();
                $uri = !empty($uri) ? $uri : '';
                if ($setting == 'image_link') {
                  $item->set('fc_path', $uri);
                }
                elseif ($setting == 'slideshow_caption_link') {
                  $item->set($path, $uri);
                }
              }
              break;

            case 'file':
              $target_id = $item->getFieldCollectionItem()->get($image_field)->first()->getValue()['target_id'];
              $file = File::load($target_id)->getFileUri();
              $uri = Url::fromUri(file_create_url($file));
              $uri = !empty($uri) ? $uri : '';
              if ($setting == 'image_link') {
                $item->set('fc_file_path', $uri);
              }
              elseif ($setting == 'slideshow_caption_link') {
                $item->set($path, $uri);
              }
              break;

            case 'colorbox':
              $attrib = array();

              // Check if we need a thumbnail and change the link.
              $entity = $item->getEntity();
              $target_id = $item->getFieldCollectionItem()->get($image_field)->first()->getValue()['target_id'];
              $file = File::load($target_id)->getFileUri();
              $uri = Url::fromUri(file_create_url($file));
              $uri = !empty($uri) ? $uri : '';
              if ($this->getSetting('slideshow_colorbox_image_style') != '') {
                $uri = ImageStyle::load($this->getSetting('slideshow_colorbox_image_style'))->buildUrl($file);
                $attrib['uri'] = $uri;
              }
              else {
                $attrib['uri'] = $uri->getUri();
              }

              // Add correct attributes.
              $attrib['attributes'] = array(
                'class' => array('colorbox'),
                'rel'   => 'field-collection-slideshow[' . 'nid' . '-' . $entity->id() . ']',
              );

              if ($this->getSetting('slideshow_caption') != '' && isset($item_settings[$delta]['caption'])) {
                $attrib['attributes']['title'] = $item_settings[$delta]['caption'];
              }

              $colorbox_slideshow = $this->getSetting('slideshow_colorbox_slideshow');

              if (isset($colorbox_slideshow) && $colorbox_slideshow != '') {
                $attrib['attributes']['class'] = array('colorbox');
                $attrib['uri'] .= (strpos($attrib['uri'], '?') === FALSE) ? '?' : '&';
                $attrib['uri'] .= 'slideshow=true&slideshowAuto=' . (($this->getSetting('slideshow_colorbox_slideshow') == 'automatic') ? 'true' : 'false') . '&slideshowSpeed=' . $this->getSetting('slideshow_colorbox_slideshow_speed') . '&speed=' . $this->getSetting('slideshow_colorbox_speed') . '&transition=' . $this->getSetting('slideshow_colorbox_transition');
              }
              $file_delta = $item->getName();
              $items[$file_delta]->set($path, $attrib);
              break;
          }
        }
      }

      if ($this->getSetting('slideshow_pager') == 'image') {
        $target_id = $item->getFieldCollectionItem()->get($image_field)->first()->getValue()['target_id'];
        $file = File::load($target_id)->getFileUri();
        $item->set('fc_thumbnail_path', $file);
      }
    }

    $pager = array(
      '#theme'                => 'field_slideshow_pager',
      '#items'                => $items,
      '#pager'                => $this->getSetting('slideshow_pager'),
      '#pager_image_style'    => $this->getSetting('slideshow_pager_image_style'),
      '#slideshow_id'         => $slideshow_count,
      '#check' => 'field_collection',
      '#fc_image_field'          => $this->getSetting('image_field'),
    );
    $controls = array(
      '#theme'                => 'field_slideshow_controls',
      '#slideshow_id'         => $slideshow_count,
      '#controls_pause'       => $this->getSetting('slideshow_controls_pause'),
    );
    $elements[] = array(
      '#theme'                => 'field_slideshow',
      '#items'                => $items,
      '#image_style'          => $this->getSetting('image_style'),
      '#image'                => $render_item,
      '#order'                => $this->getSetting('slideshow_order'),
      '#controls'             => ($this->getSetting('slideshow_controls') == 1 ? $controls : array()),
      '#controls_position'    => $this->getSetting('slideshow_controls_position'),
      '#pager'                => $this->getSetting('slideshow_pager') !== '' ? $pager : array(),
      '#pager_position'       => $this->getSetting('slideshow_pager_position'),
      '#entity'               => $entity,
      '#slideshow_id'         => $slideshow_count,
      '#js_variables'         => array(
        'fx'                   => $this->getSetting('slideshow_fx'),
        'speed'                => $this->getSetting('slideshow_speed'),
        'timeout'              => $this->getSetting('slideshow_timeout'),
        'pause'                => $this->getSetting('slideshow_pause'),
        'start_on_hover'       => $this->getSetting('slideshow_start_on_hover'),
        // Need to access the following variables in js too.
        'pager'                => $this->getSetting('slideshow_pager'),
        'controls'             => $this->getSetting('slideshow_controls') === 1 ? $controls : array(),
      ),
    );
    return $elements;
  }

  /**
   * Helper function.
   */
  public function fieldSlideshowGetFields($support_type, $bundle, $field_name) {
    $link  = array();
    $map   = \Drupal::entityManager()->getFieldMap();
    $map   = $map['field_collection_item'];
    $field_machinename = array();

    foreach ($map as $key => $value) {
      if ($value['type'] == $support_type) {
        $field_machinename[$key] = $value['bundles'];
      }
    }
    foreach ($field_machinename as $key => $values) {
      foreach ($values as $innerkey => $innervalue) {
        if ($innerkey == $field_name) {
          $link[$key] = $key;
        }
      }
    }
    return $link;
  }

}
