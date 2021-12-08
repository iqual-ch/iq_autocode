<?php

namespace Drupal\iq_autocode\Plugin\Field\FieldFormatter;

use Drupal\barcodes\Plugin\Field\FieldFormatter\Barcode;
use Drupal\Core\Form\FormStateInterface;

/**
 * Plugin implementation of the 'iq_autocode' formatter.
 *
 * @FieldFormatter(
 *   id = "iq_autocode",
 *   label = @Translation("Autocode formatter"),
 *   field_types = {
 *     "iq_autocode",
 *   }
 * )
 */
class AutoCodeFormatter extends Barcode {

  /**
   *
   */
  public function settingsForm(array $form, FormStateInterface $form_state) {
    $settings = parent::settingsForm($form, $form_state);
    $settings['type']['#type'] = 'hidden';
    return $settings;
  }

}
