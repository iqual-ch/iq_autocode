<?php

namespace Drupal\autoshortqr\Plugin\views\field;

use Drupal\views\Plugin\views\field\Url;
use Drupal\views\ResultRow;
use Drupal\Core\Link;
use Drupal\Core\Url as CoreUrl;

/**
 * A handler to provide proper displays for node qr code.
 *
 * @ingroup views_field_handlers
 *
 * @ViewsField("autoshortqr_short_link")
 */
class AutoCodeShortLinkField extends Url {

  /**
   * {@inheritdoc}
   */
  public function getValue(ResultRow $values, $field = NULL) {
    $entity = $this->getEntity($values);
    return $entity->autoshortqr_short_link->uri;
  }

  /**
   * {@inheritdoc}
   */
  public function render(ResultRow $values) {
    $value = $this->getValue($values);
    if (!empty($this->options['display_as_link'])) {
      return Link::fromTextAndUrl($this->sanitizeValue($value), CoreUrl::fromUri($value))->toString();
    }
    else {
      return $this->sanitizeValue($value, 'url');
    }
  }

  /**
   * {@inheritdoc}
   */
  public function query() {
    // This function exists to override parent query function.
    // Do nothing.
  }

}
