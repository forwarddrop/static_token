<?php

/**
 * @file
 * Contains \Drupal\Tests\static_token\Unit\StaticTokenAccessCheck.
 */

namespace Drupal\Tests\static_token\Unit;

use Drupal\Core\Access\AccessResultInterface;
use Drupal\Core\Cache\Context\CacheContextsManager;
use Drupal\Core\Routing\RouteMatchInterface;
use Drupal\static_token\StaticTokenAccessCheck;
use Drupal\static_token\StaticTokenGenerator;
use Prophecy\Argument;
use Symfony\Component\DependencyInjection\Container;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Route;

/**
 * @coversDefaultClass \Drupal\static_token\StaticTokenAccessCheck
 */
class StaticTokenAccessCheckTest extends \PHPUnit_Framework_TestCase {

  /**
   * {@inheritdoc}
   */
  protected function setUp() {
    parent::setUp();

    $container = new Container();
    $cache_context_manager = $this->prophesize(CacheContextsManager::class);
    $cache_context_manager->assertValidTokens(Argument::any())->willReturn(TRUE);
    $container->set('cache_contexts_manager', $cache_context_manager->reveal());
    \Drupal::setContainer($container);
  }


  /**
   * @covers ::access
   */
  public function testAccessWithInvalidToken() {
    $token_generator = $this->prophesize(StaticTokenGenerator::class);
    $token_generator->validate('KhvAQwnNyEhrD4BjR2skBm_axpbBTYnyDbu8EUJNPQE', 314)
      ->willReturn(TRUE);
    $token_generator->validate(Argument::any(), Argument::any())
      ->willReturn(FALSE);

    $access_check = new StaticTokenAccessCheck($token_generator->reveal());

    $route = new Route('/example/{id}', [], ['_static_token' => '{id}']);
    $request = Request::create('/example/314', 'GET', ['static_token' => '12345123123123123123123123123213123123']);
    $route_match = $this->prophesize(RouteMatchInterface::class);
    $route_match->getRawParameters()->willReturn(['id' => '314']);

    $result = $access_check->access($route, $request, $route_match->reveal());
    $this->assertInstanceOf(AccessResultInterface::class, $result);
    $this->assertFalse($result->isAllowed());
  }

  /**
   * @covers ::access
   */
  public function testAccessWithValidToken() {
    $token_generator = $this->prophesize(StaticTokenGenerator::class);
    $token_generator->validate('KhvAQwnNyEhrD4BjR2skBm_axpbBTYnyDbu8EUJNPQE', 314)
      ->willReturn(TRUE);
    $token_generator->validate(Argument::any(), Argument::any())
      ->willReturn(FALSE);

    $access_check = new StaticTokenAccessCheck($token_generator->reveal());

    $route = new Route('/example/{id}', [], ['_static_token' => '{id}']);
    $request = Request::create('/example/314', 'GET', ['static_token' => 'KhvAQwnNyEhrD4BjR2skBm_axpbBTYnyDbu8EUJNPQE']);
    $route_match = $this->prophesize(RouteMatchInterface::class);
    $route_match->getRawParameters()->willReturn(['id' => '314']);

    $result = $access_check->access($route, $request, $route_match->reveal());
    $this->assertInstanceOf(AccessResultInterface::class, $result);
    $this->assertTrue($result->isAllowed());
  }

}
