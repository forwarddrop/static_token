<?php

/**
 * @file
 * Contains \Drupal\static_token\StaticTokenAccessCheck.
 */

namespace Drupal\static_token;

use Drupal\Core\Access\AccessResult;
use Drupal\Core\Routing\Access\AccessInterface;
use Drupal\Core\Routing\RouteMatchInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Route;

class StaticTokenAccessCheck implements AccessInterface {

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
   * Checks access based on a token for the request.
   *
   * @param \Symfony\Component\Routing\Route $route
   *   The route to check against.
   * @param \Symfony\Component\HttpFoundation\Request $request
   *   The request object.
   * @param \Drupal\Core\Routing\RouteMatchInterface $route_match
   *   The route match object.
   *
   * @return \Drupal\Core\Access\AccessResultInterface
   *   The access result.
   */
  public function access(Route $route, Request $request, RouteMatchInterface $route_match) {
    if (!$request->query->has('static_token')) {
      return AccessResult::forbidden()->addCacheContexts(['url.query_args:static_token']);
    }

    $token = $request->query->get('static_token');
    $parameters = $route_match->getRawParameters();
    $static_token_configuration = $route->getRequirement('_static_token');
    if ($static_token_configuration === 'TRUE') {
      $access_result = AccessResult::allowedIf($this->staticTokenGenerator->validate($token, ''));
    }
    else {
      preg_match_all('({[\w]+})', $static_token_configuration, $matches);

      $values = [];
      foreach ($matches as $match) {
        $key = str_replace(['{', '}'], '', $match[0]);
        $values[] = $parameters[$key];
      }
      $access_result = AccessResult::allowedIf($this->staticTokenGenerator->validate($token, implode('/', $values)));
    }
    return $access_result->addCacheContexts(['url.query_args:static_token']);
  }

}
