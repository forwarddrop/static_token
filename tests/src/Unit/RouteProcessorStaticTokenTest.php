<?php

/**
 * @file
 * Contains \Drupal\Tests\static_token\Unit\RouteProcessorStaticTokenTest.
 */

namespace Drupal\Tests\static_token\Unit;

use Drupal\static_token\RouteProcessorStaticToken;
use Drupal\static_token\StaticTokenGenerator;
use Symfony\Component\Routing\Route;

/**
 * @coversDefaultClass \Drupal\static_token\RouteProcessorStaticToken
 * @group static_token
 */
class RouteProcessorStaticTokenTest extends \PHPUnit_Framework_TestCase {

  /**
   * @dataProvider providerTestProcessOutbound
   */
  public function testProcessOutbound(Route $route, $parameters, $expected_parameters) {
    $static_token_generator = $this->prophesize(StaticTokenGenerator::class);
    $static_token_generator->get('')->willReturn('uLGFwt13Pc1OmqE7FTbrywoIKL9wl5JpD4zGEpbfJhs');
    $static_token_generator->get(314)->willReturn('KhvAQwnNyEhrD4BjR2skBm_axpbBTYnyDbu8EUJNPQE');

    $route_processor_static = new RouteProcessorStaticToken($static_token_generator->reveal());

    $route_processor_static->processOutbound('test_route', $route, $parameters);
    $this->assertEquals($expected_parameters, $parameters);
  }

  public function providerTestProcessOutbound() {
    $data = [];
    $data['no-static-token'] = [new Route('/example'), [], []];
    $data['no-static-token-with-parameters'] = [new Route('/example/{id}'), ['id' => 314], ['id' => 314]];
    $data['static-token'] = [new Route('/example', [], ['_static_token' => 'TRUE']), [], ['static_token' => 'uLGFwt13Pc1OmqE7FTbrywoIKL9wl5JpD4zGEpbfJhs']];
    $data['static-token-with-parameter'] = [new Route('/example/{id}', [], ['_static_token' => '{id}']), ['id' => '314'], ['id' => '314', 'static_token' => 'KhvAQwnNyEhrD4BjR2skBm_axpbBTYnyDbu8EUJNPQE']];

    return $data;
  }

}
