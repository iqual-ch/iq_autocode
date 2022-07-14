<?php

namespace Drupal\autoshortqr\Plugin\Field\FieldType;

use Drupal\autoshortqr\UserThirdpartyWrapper;
use Drupal\taxonomy\Entity\Vocabulary;
use Drupal\link\Plugin\Field\FieldType\LinkItem;

/**
 * Variant of the 'link' field that links to the current company.
 *
 * @FieldType(
 *   id = "autoshortqr",
 *   label = @Translation("Autocode"),
 *   description = @Translation("A link to the current company that is associated with the entity."),
 *   default_widget = "autoshortqr",
 *   default_formatter = "autoshortqr",
 *   constraints = {"LinkType" = {}, "LinkAccess" = {}, "LinkExternalProtocols" = {}, "LinkNotExistingInternal" = {}}
 * )
 */
class AutoCodeLinkItem extends LinkItem {

  /**
   * Whether or not the value has been calculated.
   *
   * @var bool
   */
  protected $isCalculated = FALSE;

  /**
   * {@inheritdoc}
   */
  public function __get($name) {
    $this->ensureCalculated();
    return parent::__get($name);
  }

  /**
   * {@inheritdoc}
   */
  public function isEmpty() {
    $this->ensureCalculated();
    return parent::isEmpty();
  }

  /**
   * {@inheritdoc}
   */
  public function getValue() {
    $this->ensureCalculated();
    return parent::getValue();
  }

  /**
   * Calculates the value of the field and sets it.
   */
  protected function ensureCalculated() {
    if (!$this->isCalculated) {
      $entity = $this->getEntity();
      if (!$entity->isNew()) {
        if ($entity->getEntityTypeId() == 'node') {
          $host = $entity->type->entity->getThirdPartySetting('autoshortqr', 'qr_base_domain', \Drupal::request()->getSchemeAndHttpHost());
          $prefix = 'nc';
        }
        if ($entity->getEntityTypeId() == 'taxonomy_term') {
          $host = Vocabulary::load($entity->bundle())->getThirdPartySetting('autoshortqr', 'qr_base_domain', \Drupal::request()->getSchemeAndHttpHost());
          $prefix = 'tc';
        }
        if ($entity->getEntityTypeId() == 'user') {
          $host = (new UserThirdpartyWrapper())->getThirdPartySetting('autoshortqr', 'qr_base_domain', \Drupal::request()->getSchemeAndHttpHost());
          $prefix = 'uc';
        }
        if ($entity->getEntityTypeId() == 'redirect') {
          $host = (new UserThirdpartyWrapper())->getThirdPartySetting('autoshortqr', 'qr_base_domain', \Drupal::request()->getSchemeAndHttpHost());
          $prefix = 'rc';
        }

        $value = [
          'uri' => $host . '/' . $prefix . '/' . base_convert($entity->id(), 10, 36),
          'title' => t('Self qr link'),
        ];
        $this->setValue($value);
        $this->isCalculated = TRUE;
      }

    }
  }

}
