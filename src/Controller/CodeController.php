<?php

namespace Drupal\iq_autocode\Controller;

use Drupal\Core\Config\Entity\ThirdpartySettingsInterface;
use Drupal\Core\Entity\EntityInterface;
use Drupal\iq_autocode\UserThirdpartyWrapper;
use Drupal\taxonomy\Entity\Vocabulary;
use Drupal\taxonomy\Entity\Term;
use Drupal\user\Entity\User;
use Drupal\node\Entity\Node;
use Drupal\Core\Controller\ControllerBase;
use Drupal\Core\Url;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Response;

/**
 * Resolves requests to short urls.
 */
class CodeController extends ControllerBase {

  /**
   * The possible utm variables.
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
   * @return \Symfony\Component\HttpFoundation\Response
   *   The response.
   */
  public function resolveNodeUrlQr(string $short_value) {
    return $this->resolveNodeUrl($short_value, 'qr');
  }

  /**
   * Resolves a short value to a user (or front page).
   *
   * @param string $short_value
   *   The short value to resolve.
   *
   * @return \Symfony\Component\HttpFoundation\Response
   *   The response.
   */
  public function resolveUserUrlQr(string $short_value) {
    return $this->resolveUserUrl($short_value, 'qr');
  }

  /**
   * Resolves a short value to a term (or front page).
   *
   * @param string $short_value
   *   The short value to resolve.
   *
   * @return \Symfony\Component\HttpFoundation\Response
   *   The response.
   */
  public function resolveTermUrlQr(string $short_value) {
    return $this->resolveTermUrl($short_value, 'qr');
  }

  /**
   * Returns a download response for the node qr code.
   *
   * @param string $short_value
   *   The short value to resolve.
   *
   * @return \Symfony\Component\HttpFoundation\Response
   *   The response.
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
   * Returns a download response for the user qr code.
   *
   * @param string $short_value
   *   The short value to resolve.
   *
   * @return \Symfony\Component\HttpFoundation\Response
   *   The response.
   */
  public function downloadUserQr(string $short_value) {
    $id = intval($short_value, 36);
    if (is_numeric($id)) {
      $entity = User::load($id);
      if (!empty($entity) && (new UserThirdpartyWrapper())->getThirdPartySetting('iq_autocode', 'qr_enable', FALSE)) {
        return $this->sendQrCode($entity);
      }
    }
  }

  /**
   * Returns a download response for the term qr code.
   *
   * @param string $short_value
   *   The short value to resolve.
   *
   * @return \Symfony\Component\HttpFoundation\Response
   *   The response.
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
   * Resolves a short value to a node (or front page).
   *
   * @param string $short_value
   *   The short value to resolve.
   *
   * @return \Symfony\Component\HttpFoundation\Response
   *   The response.
   */
  public function resolveNodeUrlShort(string $short_value) {
    return $this->resolveNodeUrl($short_value, 'short');
  }

  /**
   * Resolves a short value to a user (or front page).
   *
   * @param string $short_value
   *   The short value to resolve.
   *
   * @return \Symfony\Component\HttpFoundation\Response
   *   The response.
   */
  public function resolveUserUrlShort(string $short_value) {
    return $this->resolveUserUrl($short_value, 'short');
  }

  /**
   * Resolves a short value to a term (or front page).
   *
   * @param string $short_value
   *   The short value to resolve.
   *
   * @return \Symfony\Component\HttpFoundation\Response
   *   The response.
   */
  public function resolveTermUrlShort(string $short_value) {
    return $this->resolveTermUrl($short_value, 'short');
  }

  /**
   * Helper function to create the qr download response.
   *
   * @param \Drupal\Core\Entity\EntityInterface $entity
   *   The entity to create the qr code.
   *
   * @return \Symfony\Component\HttpFoundation\Response
   *   The response.
   */
  protected function sendQrCode(EntityInterface $entity) {
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
   * Helper to resolve a short url to the node and type (qr/short).
   *
   * @param string $short_value
   *   The short value to resolve.
   * @param string $type
   *   The type of link to resolve (qr or short).
   *
   * @return Symfony\Component\HttpFoundation\RedirectResponse
   *   The redirect response to the node.
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
   * Helper to resolve a short url to the user and type (qr/short).
   *
   * @param string $short_value
   *   The short value to resolve.
   * @param string $type
   *   The type of link to resolve (qr or short).
   *
   * @return Symfony\Component\HttpFoundation\RedirectResponse
   *   The redirect response to the user.
   */
  protected function resolveUserUrl(string $short_value, string $type) {
    $url = Url::fromRoute('<front>', [], ['absolute' => TRUE])->toString();
    $url = Url::fromRoute('<front>', [], ['absolute' => TRUE])->toString();
    $id = intval($short_value, 36);
    if (is_numeric($id)) {
      $entity = User::load($id);
      if (!empty($entity)) {
        $settings = (new UserThirdpartyWrapper())->getThirdPartySettings('iq_autocode');
        $url = $this->createURL($entity, $settings, $type);
      }
    }
    return new RedirectResponse($url);
  }

  /**
   * Helper to resolve a short url to the term and type (qr/short).
   *
   * @param string $short_value
   *   The short value to resolve.
   * @param string $type
   *   The type of link to resolve (qr or short).
   *
   * @return Symfony\Component\HttpFoundation\RedirectResponse
   *   The redirect response to the term.
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
   * Helper function to create the url to the entity.
   *
   * @param \Drupal\Core\Entity\EntityInterface $entity
   *   The targeted entity.
   * @param array $settings
   *   The settings for this entity.
   * @param string $type
   *   The tzpe of short url (qr or short).
   *
   * @return \Drupal\Core\Url
   *   The Url to the entity or the front page.
   */
  protected function createUrl(EntityInterface $entity, array $settings, string $type) {
    $tokenService = \Drupal::service('token');
    if (!empty($settings[$type . '_enable']) && $settings[$type . '_enable']) {
      $query = [];
      foreach (self::UTM_VARS as $utmvar) {
        $settingName = $type . '_' . $utmvar;
        $value = $value = $tokenService->replace($settings[$settingName], [$entity->getEntityTypeId() => $entity], ['clear' => TRUE]);
        if (!empty($value)) {
          $query[$utmvar] = $value;
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
