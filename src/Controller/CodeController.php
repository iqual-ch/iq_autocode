<?php

namespace Drupal\iq_autocode\Controller;

use Drupal\taxonomy\Entity\Vocabulary;
use Drupal\taxonomy\Entity\Term;
use Drupal\user\Entity\User;
use Drupal\node\Entity\Node;
use Drupal\Core\Controller\ControllerBase;
use Drupal\Core\Url;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Response;

/**
 *
 */
class CodeController extends ControllerBase {

  /**
   * 
   */
  public const UTM_VARS = [
    'utm_source', 'utm_medium', 'utm_campagin', 'utm_content', 'utm_term',
  ];

  /**
   * Resolves a short value to a node (or front page).
   *
   * @param string $short_value
   *   The short value to resolve.
   *
   * @return void
   */
  public function resolveNodeUrlQr(string $short_value) {
    return $this->resolveNodeUrl($short_value, 'qr');
  }

  /**
   *
   */
  public function resolveUserUrlQr(string $short_value) {
    return $this->resolveUserUrl($short_value, 'qr');
  }

  /**
   *
   */
  public function resolveTermUrlQr(string $short_value) {
    return $this->resolveTermUrl($short_value, 'qr');
  }

  /**
   *
   */
  public function downloadNodeQr(string $short_value) {
    $id = intval($short_value, 36);
    if (is_numeric($id)) {
      $entity = Node::load($id);
      if (!empty($entity) && $entity->type->entity->getThirdPartySetting('iq_autocode', 'qr_enable', FALSE)) {
        return $this->sendQrCode($entity);
      }
    }
  }

  /**
   *
   */
  public function downloadUserQr(string $short_value) {
    $id = intval($short_value, 36);
    if (is_numeric($id)) {
      $entity = User::load($id);
      if (!empty($entity) && $entity->type->entity->getThirdPartySetting('iq_autocode', 'qr_enable', FALSE)) {
        return $this->sendQrCode($entity);
      }
    }
  }

  /**
   *
   */
  public function downloadTermQr(string $short_value) {
    $id = intval($short_value, 36);
    if (is_numeric($id)) {
      $entity = Term::load($id);

      if (!empty($entity) && Vocabulary::load($entity->bundle())->getThirdPartySetting('iq_autocode', 'qr_enable', FALSE)) {
        return $this->sendQrCode($entity);
      }
    }
  }

  /**
   *
   */
  public function resolveNodeUrlShort(string $short_value) {
    return $this->resolveNodeUrl($short_value, 'short');
  }

  /**
   *
   */
  public function resolveUserUrlShort(string $short_value) {
    return $this->resolveUserUrl($short_value, 'short');
  }

  /**
   *
   */
  public function resolveTermUrlShort(string $short_value) {
    return $this->resolveTermUrl($short_value, 'short');
  }

  /**
   *
   */
  protected function sendQrCode($entity) {
    $svgCode = $entity->iq_autocode->view([
      'type' => 'iq_autocode',
      'label' => t('QR Code'),
      'settings' => [
        'height' => 400,
        'width' => 400,
      ],
    ]);
    $data = $svgCode[0]['#svg'];
    // Generate response for given data file.
    $response = new Response($data, 200);
    $response->headers->set('content-type', 'image/svg+xml');
    $response->headers->set('Content-Length', strlen($data));
    $response->headers->set('Content-Disposition', 'attachment; filename=qr_' . $entity->getEntityTypeId() . '_' . $entity->id() . '.svg');
    return $response;
  }

  /**
   *
   */
  protected function resolveNodeUrl(string $short_value, string $type) {
    $url = Url::fromRoute('<front>', [], ['absolute' => TRUE])->toString();
    $id = intval($short_value, 36);
    if (is_numeric($id)) {
      $entity = Node::load($id);
      if (!empty($entity)) {
        $settings = $entity->type->entity->getThirdPartySettings('iq_autocode');
        $url = $this->createURL($entity, $settings, $type);
      }
    }
    return new RedirectResponse($url);
  }

  /**
   * @todo Implement for users.
   */
  protected function resolveUserUrl(string $short_value, string $type) {
    $url = Url::fromRoute('<front>', [], ['absolute' => TRUE])->toString();

    return new RedirectResponse($url);
  }

  /**
   *
   */
  protected function resolveTermUrl(string $short_value, string $type) {
    $url = Url::fromRoute('<front>', [], ['absolute' => TRUE])->toString();
    $id = intval($short_value, 36);
    if (is_numeric($id)) {
      $entity = Term::load($id);
      if (!empty($entity)) {
        $settings = Vocabulary::load($entity->bundle())->getThirdPartySettings('iq_autocode');
        $url = $this->createURL($entity, $settings, $type);
      }
    }
    return new RedirectResponse($url);
  }

  /**
   *
   */
  protected function createUrl($entity, $settings, $type) {
    if (!empty($settings[$type . '_enable']) && $settings[$type . '_enable']) {
      $query = [];
      foreach (self::UTM_VARS as $utmvar) {
        $settingName = $type . '_' . $utmvar;
        if (!empty($settings[$settingName])) {
          $query[$utmvar] = $settings[$settingName];
        }
      }
      return $entity->toURL(
      'canonical',
      [
        'query' => $query,
      ]
        )
        ->toString();
    }
    return Url::fromRoute('<front>', [], ['absolute' => TRUE])->toString();

  }

}
