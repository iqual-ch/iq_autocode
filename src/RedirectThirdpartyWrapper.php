<?php

namespace Drupal\iq_autocode;

/**
 * Emulates ThirdpartySettingsInterface for the redirect type.
 *
 * This simplifies the rest of the module code.
 */
class RedirectThirdpartyWrapper extends UserThirdpartyWrapper {

  /**
   * The config prefix.
   *
   * @var string
   */
  protected $prefix = 'redirect.';

  /**
   * Returns the redirect entity type id "redirect".
   */
  public function getEntityTypeId() {
    return 'redirect';
  }

}
