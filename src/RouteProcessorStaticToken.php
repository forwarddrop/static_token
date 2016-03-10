<?php

/**
 * @file
 * Contains \Drupal\static_token\RouteProcessorStaticToken.
 */

namespace Drupal\static_token;

use Drupal\Core\Render\BubbleableMetadata;
use Drupal\Core\RouteProcessor\OutboundRouteProcessorInterface;
use Symfony\Component\Routing\Route;

class RouteProcessorStaticToken implements OutboundRouteProcessorInterface {

  /**
   * The static token generator.
   *
   * @var \Drupal\static_token\StaticTokenGenerator
   */
  protected $staticTokenGenerator;

  /**
   * Creates a new RouteProcessorStaticToken instance.
   *
   * @param \Drupal\static_token\StaticTokenGenerator $staticTokenGenerator
   *   The static token generator.
   */
  public function __construct(StaticTokenGenerator $staticTokenGenerator) {
    $this->staticTokenGenerator = $staticTokenGenerator;
  }

  /**
   * {@inheritdoc}
   */
  public function processOutbound($route_name, Route $route, array &$parameters, BubbleableMetadata $bubbleable_metadata = NULL) {
    if (!$route->hasRequirement('_static_token')) {
      return;
    }

    $static_token_configuration = $route->getRequirement('_static_token');
    if ($static_token_configuration === 'TRUE') {
      $parameters['static_token'] = $this->staticTokenGenerator->get('');
    }
    else {
      preg_match_all('({[\w]+})', $static_token_configuration, $matches);

      $values = [];
      foreach ($matches as $match) {
        $key = str_replace(['{', '}'], '', $match[0]);
        $values[] = $parameters[$key];
      }
      $parameters['static_token'] = $this->staticTokenGenerator->get(implode('/', $values));
    }
  }

}
