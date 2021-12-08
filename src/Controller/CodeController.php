<?php

namespace Drupal\iq_autocode\Controller;

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
   * Resolves a short value to a node (or front page).
   *
   * @param string $short_value
   *   The short value to resolve.
   *
   * @return void
   */
  public function resolveNodeUrl(string $shortValue) {
    $url = Url::fromRoute('<front>', [], ['absolute' => TRUE])->toString();
    $nodeId = intval($shortValue, 36);
    if (is_numeric($nodeId)) {
      $entity = Node::load($nodeId);
      $url = $this->createURL($entity);
    }
    return new RedirectResponse($url);
  }

  /**
   *
   */
  public function resolveUserUrl(string $shortValue) {}

  /**
   *
   */
  public function resolveTermUrl(string $shortValue) {}

  /**
   *
   */
  protected function createUrl($entity) {
    if (!empty($entity)) {

      return $entity->toURL(
      'canonical',
      [
        'query' =>
        [
          'utm_source' => 'website',
          'utm_medium' => 'qr',
          'utm_campaign' => 'qr',
          'utm_content' => $entity->getLabel(),
          'utm_term' => $entity->getLabel(),
        ],
      ]
      )
        ->toString();
    }
    return Url::fromRoute('<front>', [], ['absolute' => TRUE])->toString();
  }

  /**
   *
   */
  public function downloadNodeImage(string $short_value) {
    $nodeId = intval($short_value, 36);
    if (is_numeric($nodeId)) {
      $entity = Node::load($nodeId);
      if (!empty($entity) && $entity->type->entity->getThirdPartySetting('iq_autocode', 'qr_enable', FALSE)) {
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
        $response->headers->set('Content-Disposition', 'attachment; filename=qr_' . $entity->id() . '.svg');
        $response->send();

      }
    }

  }

}
