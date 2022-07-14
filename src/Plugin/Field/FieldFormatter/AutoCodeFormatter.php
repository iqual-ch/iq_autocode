<?php

namespace Drupal\autoshortqr\Plugin\Field\FieldFormatter;

use Drupal\barcodes\Plugin\Field\FieldFormatter\Barcode;
use Drupal\Core\Form\FormStateInterface;

/**
 * Plugin implementation of the 'autoshortqr' formatter.
 *
 * @FieldFormatter(
 *   id = "autoshortqr",
 *   label = @Translation("Autocode formatter"),
 *   field_types = {
 *     "autoshortqr",
 *   }
 * )
 */
class AutoCodeFormatter extends Barcode {

  /**
   * {@inheritDoc}
   */
  public function settingsForm(array $form, FormStateInterface $form_state) {
    // Hide the type selection from the settings form.
    $settings = parent::settingsForm($form, $form_state);
    $settings['type']['#type'] = 'hidden';
    return $settings;
  }

}
