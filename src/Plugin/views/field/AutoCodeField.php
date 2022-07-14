<?php

namespace Drupal\autoshortqr\Plugin\views\field;

use Drupal\views\ResultRow;
use Drupal\views\Plugin\views\field\FieldPluginBase;

/**
 * A handler to provide proper displays for node qr code.
 *
 * @ingroup views_field_handlers
 *
 * @ViewsField("autoshortqr")
 */
class AutoCodeField extends FieldPluginBase {

  /**
   * {@inheritdoc}
   */
  public function render(ResultRow $values) {
    $entity = $this->getEntity($values);

    // If we have the right entity type, render the code field.
    if (!empty($entity) && in_array($entity->getEntityTypeId(), ['node', 'taxonomy_term', 'user', 'redirect'])) {
      $value = $entity->autoshortqr->view([
        'type' => 'autoshortqr',
        'label' => '',
        'settings' => [
          'height' => 400,
          'width' => 400,
        ],
      ]);
      $prefix = 'nc';

      if ($entity->getEntityTypeId() == 'taxonomy_term') {
        $prefix = 'tc';
      }
      if ($entity->getEntityTypeId() == 'user') {
        $prefix = 'uc';
      }
      if ($entity->getEntityTypeId() == 'redirect') {
        $prefix = 'rc';
      }
      $value['#prefix'] = '<a href="/autoshortqr/' . $prefix . '/' . base_convert($entity->id(), 10, 36) . '" target="_blank">';
      $value['#suffix'] = '</a>';
    }

    return $value;
  }

  /**
   * {@inheritdoc}
   */
  public function query() {
    // This function exists to override parent query function.
    // Do nothing.
  }

}
