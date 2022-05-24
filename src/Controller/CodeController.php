<?php

namespace Drupal\iq_autocode\Controller;

use Drupal\Core\Language\LanguageInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Drupal\Core\Language\LanguageManager;
use Drupal\Core\Utility\Token;
use Drupal\Core\Entity\EntityInterface;
use Drupal\iq_autocode\UserThirdpartyWrapper;
use Drupal\iq_autocode\RedirectThirdpartyWrapper;
use Drupal\taxonomy\Entity\Vocabulary;
use Drupal\taxonomy\Entity\Term;
use Drupal\user\Entity\User;
use Drupal\node\Entity\Node;
use Drupal\Core\Controller\ControllerBase;
use Drupal\Core\Entity\ContentEntityInterface;
use Drupal\Core\Url;
use Drupal\redirect\Entity\Redirect;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Response;

/**
 * Resolves requests to short urls.
 */
class CodeController extends ControllerBase {

  /**
   * The token utility.
   *
   * @var \Drupal\Core\Utility\Token
   */
  protected $tokenService;

  /**
   * The language manager.
   *
   * @var \Drupal\Core\Language\LanguageManager
   */
  protected $languageManager;

  /**
   * The possible utm variables.
   */
  public const UTM_VARS = [
    'utm_source', 'utm_medium', 'utm_campaign', 'utm_content', 'utm_term',
  ];

  /**
   * Creates a new CodeController.
   *
   * @param \Drupal\Core\Utility\Token $token
   *   The token utility.
   * @param \Drupal\Core\Language\LanguageManager $language_manager
   *   The language manager.
   */
  public function __construct(Token $token, LanguageManager $language_manager) {
    $this->tokenService = $token;
    $this->languageManager = $language_manager;
  }

  /**
   * {@inheritDoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
          $container->get('token'),
          $container->get('language_manager')
      );
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
   * Resolves a short value to a term (or front page).
   *
   * @param string $short_value
   *   The short value to resolve.
   *
   * @return \Symfony\Component\HttpFoundation\Response
   *   The response.
   */
  public function resolveRedirectUrlQr(string $short_value) {
    return $this->resolveRedirectUrl($short_value, 'qr');
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
      /** @var \Drupal\node\Entity\NodeType $entityType */
      $entityType = $entity->type->entity;
      if (!empty($entity) && $entityType->getThirdPartySetting('iq_autocode', 'qr_enable', FALSE)) {
        return $this->sendQrCode($entity);
      }
    }
    return new Response('', 404);
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
    return new Response('', 404);
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
    return new Response('', 404);
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
  public function downloadRedirectQr(string $short_value) {
    $id = intval($short_value, 36);
    if (is_numeric($id)) {
      $entity = Redirect::load($id);
      if (!empty($entity) && (new RedirectThirdpartyWrapper())->getThirdPartySetting('iq_autocode', 'qr_enable', FALSE)) {
        return $this->sendQrCode($entity);
      }
    }
    return new Response('', 404);
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
   * Resolves a short value to a term (or front page).
   *
   * @param string $short_value
   *   The short value to resolve.
   *
   * @return \Symfony\Component\HttpFoundation\Response
   *   The response.
   */
  public function resolveRedirectUrlShort(string $short_value) {
    return $this->resolveRedirectUrl($short_value, 'short');
  }

  /**
   * Helper function to create the qr download response.
   *
   * @param \Drupal\Core\Entity\ContentEntityInterface $entity
   *   The entity to create the qr code.
   *
   * @return \Symfony\Component\HttpFoundation\Response
   *   The response.
   */
  protected function sendQrCode(ContentEntityInterface $entity) {
    $svgCode = $entity->get('iq_autocode')->view([
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
   * @return \Symfony\Component\HttpFoundation\RedirectResponse
   *   The redirect response to the node.
   */
  protected function resolveNodeUrl(string $short_value, string $type) {
    $url = Url::fromRoute('<front>', [], ['absolute' => TRUE])->toString();
    $id = intval($short_value, 36);
    if (is_numeric($id)) {
      $entity = Node::load($id);

      if (!empty($entity)) {
        /** @var \Drupal\node\Entity\NodeType $nodeType */
        $nodeType = $entity->type->entity;
        $settings = $nodeType->getThirdPartySettings('iq_autocode');
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
   * @return \Symfony\Component\HttpFoundation\RedirectResponse
   *   The redirect response to the user.
   */
  protected function resolveUserUrl(string $short_value, string $type) {
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
   * @return \Symfony\Component\HttpFoundation\RedirectResponse
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
   * Helper to resolve a short url to the user and type (qr/short).
   *
   * @param string $short_value
   *   The short value to resolve.
   * @param string $type
   *   The type of link to resolve (qr or short).
   *
   * @return \Symfony\Component\HttpFoundation\RedirectResponse
   *   The redirect response to the user.
   */
  protected function resolveRedirectUrl(string $short_value, string $type) {
    $url = Url::fromRoute('<front>', [], ['absolute' => TRUE]);
    $id = intval($short_value, 36);
    if (is_numeric($id)) {
      $entity = Redirect::load($id);
      if (!empty($entity)) {
        $settings = (new RedirectThirdpartyWrapper())->getThirdPartySettings('iq_autocode');
        if (!empty($settings[$type . '_enable'])) {
          $url = $entity->getRedirectUrl();
          $query = $url->getOption('query');
          foreach (self::UTM_VARS as $utmvar) {
            $settingName = $type . '_' . $utmvar;
            $value = $this->tokenService->replace($settings[$settingName], [$entity->getEntityTypeId() => $entity], ['clear' => TRUE]);
            if (empty($query[$utmvar]) && !empty($value)) {
              $query[$utmvar] = $value;
            }
          }
          $url->setOption('query', $query);
        }
      }
    }
    return new RedirectResponse($url->toString());
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
   * @return string
   *   The Url to the entity or the front page.
   */
  protected function createUrl(EntityInterface $entity, array $settings, string $type): string {
    if (!empty($settings[$type . '_enable'])) {
      $query = [];
      foreach (self::UTM_VARS as $utmvar) {
        $settingName = $type . '_' . $utmvar;
        $value = $this->tokenService->replace($settings[$settingName], [$entity->getEntityTypeId() => $entity], ['clear' => TRUE]);
        if (!empty($value)) {
          $query[$utmvar] = $value;
        }
      }

      // Get the current language based on content language selection settings
      // as Drupal returns default language on Entity::toUrl.
      // @see https://www.drupal.org/project/drupal/issues/3061761
      $currentLanguage = $this->languageManager->getCurrentLanguage(LanguageInterface::TYPE_CONTENT);
      return $entity->toURL(
      'canonical',
      [
        'query' => $query,
        'language' => $currentLanguage,
      ]
        )
        ->toString();
    }
    return Url::fromRoute('<front>', [], ['absolute' => TRUE])->toString();
  }

}
