<?php

/**
 * @file
 * Contains \Drupal\image\Plugin\field\formatter\ImageFormatter.
 */

namespace Drupal\image_url_formatter\Plugin\field\formatter;

use Drupal\Component\Annotation\Plugin;
use Drupal\Core\Annotation\Translation;
use Drupal\field\Plugin\Type\Formatter\FormatterBase;
use Drupal\Core\Entity\EntityInterface;

/**
 * Plugin implementation of the 'image_url' formatter.
 *
 * @Plugin(
 *   id = "image_url",
 *   module = "image_url_formatter",
 *   label = @Translation("Image Url"),
 *   field_types = {
 *     "image"
 *   },
 *   settings = {
 *     "image_style" = "",
 *     "image_link" = ""
 *   }
 * )
 */
class ImageUrlFormatter extends FormatterBase {

  /**
   * {@inheritdoc}
   */
  public function settingsForm(array $form, array &$form_state) {
    $element['url_type'] = array(
      '#title' => t('URL type'),
      '#type' => 'select',
      '#options' => array(
	    0 => t('Full URL'),
        1 => t('Absolute file path'),
        2 => t('Relative file path'),
      ),
      '#default_value' => $this->getSetting('url_type'),
    );
    //$element['url_type'][0]['#description'] = t("Like: 'http://example.com/sites/default/files/image.png'"); 
    //$element['url_type'][1]['#description'] = t("With leading slash, no base URL, like: '/sites/default/files/image.png'");
    //$element['url_type'][2]['#description'] = t("No base URL or leading slash, like: 'sites/default/files/image.png'");

    $image_styles = image_style_options(FALSE);
    $element['image_style'] = array(
      '#title' => t('Image style'),
      '#type' => 'select',
      '#default_value' => $this->getSetting('image_style'),
      '#empty_option' => t('None (original image)'),
      '#options' => $image_styles,
    );

    $link_types = array(
      'content' => t('Content'),
      'file' => t('File'),
    );
    $element['image_link'] = array(
      '#title' => t('Link image url to'),
      '#type' => 'select',
      '#default_value' => $this->getSetting('image_link'),
      '#empty_option' => t('Nothing'),
      '#options' => $link_types,
    );

    return $element;
  }

  /**
   * {@inheritdoc}
   */
  public function settingsSummary() {
    $summary = array();

    switch ($this->getSetting('url_type')) {
      case 2:
        $summary[] = t('Use relative path');
        break;

      case 1:
        $summary[] = t('Use absolute path');
        break;

      case 0:
        $summary[] = t('Use full URL');
        break;
    }

    $image_styles = image_style_options(FALSE);
    // Unset possible 'No defined styles' option.
    unset($image_styles['']);
    // Styles could be lost because of enabled/disabled modules that defines
    // their styles in code.
    $image_style_setting = $this->getSetting('image_style');
    if (isset($image_styles[$image_style_setting])) {
      $summary[] = t('URL for Image style: @style', array('@style' => $image_styles[$image_style_setting]));
    }
    else {
      $summary[] = t('Original image');
    }

    $link_types = array(
      'content' => t('Linked to content'),
      'file' => t('Linked to file'),
    );
    // Display this setting only if image is linked.
    $image_link_setting = $this->getSetting('image_link');
    if (isset($link_types[$image_link_setting])) {
      $summary[] = $link_types[$image_link_setting];
    }
    return $summary;
  }

  /**
   * {@inheritdoc}
   */
  public function viewElements(EntityInterface $entity, $langcode, array $items) {
  
    $elements = array();

    $image_link_setting = $this->getSetting('image_link');
    // Check if the formatter involves a link.
    if ($image_link_setting == 'content') {
      $uri = $entity->uri();
    }
    elseif ($image_link_setting == 'file') {
      $link_file = TRUE;
    }

    $image_style_setting = $this->getSetting('image_style');
    $url_type_setting = $this->getSetting('url_type');
    foreach ($items as $delta => $item) {
      if (isset($link_file)) {
        $uri = array(
          'path' => file_create_url($item['uri']),
          'options' => array(),
        );
      }
      $elements[$delta] = array(
        '#theme' => 'image_url_formatter',
        '#item' => $item,
        '#image_style' => $image_style_setting,
        '#url_type' => $url_type_setting,
        '#path' => isset($uri) ? $uri : '',
      );
    }

    return $elements;
  }

}
