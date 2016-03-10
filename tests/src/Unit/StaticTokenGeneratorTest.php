<?php

/**
 * @file
 * Contains \Drupal\Tests\static_token\Unit\StaticTokenGeneratorTest.
 */

namespace Drupal\Tests\static_token\Unit;

use Drupal\Component\Utility\Crypt;
use Drupal\Core\PrivateKey;
use Drupal\Core\Site\Settings;
use Drupal\static_token\StaticTokenGenerator;

/**
 * @coversDefaultClass \Drupal\static_token\StaticTokenGenerator
 * @group static_token
 */
class StaticTokenGeneratorTest extends \PHPUnit_Framework_TestCase {

  /**
   * @dataProvider providerTestGet
   */
  public function testGet($value, $expected_value) {
    $privateKey = $this->prophesize(PrivateKey::class);
    $privateKey->get()->willReturn(1234);
    new Settings(['hash_salt' => 4321]);
    $token_generator = new StaticTokenGenerator($privateKey->reveal());

    $this->assertEquals($expected_value, $token_generator->get($value));
  }

  public function providerTestGet() {
    $data = [];
    $data[] = [12345, Crypt::hmacBase64(12345, '12344321')];
    $data[] = ['hello', Crypt::hmacBase64('hello', '12344321')];
    return $data;
  }

  /**
   * @dataProvider providerTestValidate
   */
  public function testValidate($value, $token, $expected) {
    $privateKey = $this->prophesize(PrivateKey::class);
    $privateKey->get()->willReturn(1234);
    new Settings(['hash_salt' => 4321]);
    $token_generator = new StaticTokenGenerator($privateKey->reveal());

    if ($expected) {
      $this->assertTrue($token_generator->validate($token, $value));
    }
    else {
      $this->assertFalse($token_generator->validate($token, $value));
    }
  }

  public function providerTestValidate() {
    $data = [];
    $data['right-key-value'] = [12345, Crypt::hmacBase64(12345, '12344321'), TRUE];
    $data['right-value-wrong-key'] = [12345, Crypt::hmacBase64(12345, '12345321'), FALSE];
    $data['right-key-wrong-value'] = [12345, Crypt::hmacBase64(123456, '12344321'), FALSE];

    $data['right-key-value2'] = ['hello', Crypt::hmacBase64('hello', '12344321'), TRUE];
    $data['right-value-wrong-key2'] = ['hello', Crypt::hmacBase64('hello', '12345321'), FALSE];
    $data['right-key-wrong-value2'] = ['hello', Crypt::hmacBase64('hellop', '12344321'), FALSE];

    return $data;
  }

}
