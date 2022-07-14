<?php

namespace Drupal\autoshortqr;

use Drupal\Core\Config\Entity\ThirdPartySettingsInterface;

/**
 * Emulates ThirdpartySettingsInterface for the user type.
 *
 * This simplifies the rest of the module code.
 */
class UserThirdpartyWrapper implements ThirdPartySettingsInterface {

  /**
   * The config prefix.
   *
   * @var string
   */
  protected $prefix = 'user.';

  /**
   * Config keys.
   *
   * @var array
   */
  protected const KEYS = [
    'qr_enable',
    'qr_base_domain',
    'qr_show',
    'qr_show_url',
    'qr_utm_source',
    'qr_utm_medium',
    'qr_utm_campaign',
    'qr_utm_content',
    'qr_utm_term',
    'short_enable',
    'short_base_domain',
    'short_show_url',
    'short_utm_source',
    'short_utm_medium',
    'short_utm_campaign',
    'short_utm_content',
    'short_utm_term',
  ];

  /**
   * Create a new UserThirdpartyWrapper.
   */
  public function __construct() {
    $this->config = \Drupal::service('config.factory')->getEditable('autoshortqr.settings');
  }

  /**
   * On descruction, save the config.
   */
  public function __destruct() {
    $this->config->save();
  }

  /**
   * {@inheritDoc}
   */
  public function setThirdPartySetting($module, $key, $value) {
    $this->config->set($this->prefix . $key, $value);
  }

  /**
   * {@inheritDoc}
   */
  public function getThirdPartySetting($module, $key, $default = NULL) {
    $value = $this->config->get($this->prefix . $key);
    return empty($value) ? $default : $value;
  }

  /**
   * {@inheritDoc}
   */
  public function getThirdPartySettings($module) {
    $settings = [];
    foreach (self::KEYS as $key) {
      $settings[$key] = $this->config->get($this->prefix . $key);
    }
    return $settings;
  }

  /**
   * {@inheritDoc}
   */
  public function unsetThirdPartySetting($module, $key) {
    $this->config->set($this->prefix . $key, NULL);
  }

  /**
   * {@inheritDoc}
   */
  public function getThirdPartyProviders() {
    return [];
  }

  /**
   * Returns the user entity type id "user".
   */
  public function getEntityTypeId() {
    return 'user';
  }

}
