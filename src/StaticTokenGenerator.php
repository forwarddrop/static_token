<?php

/**
 * @file
 * Contains \Drupal\static_token\StaticTokenGenerator.
 */

namespace Drupal\static_token;
use Drupal\Component\Utility\Crypt;
use Drupal\Core\PrivateKey;
use Drupal\Core\Site\Settings;

/**
 * Generates and validates static tokens.
 *
 * Static tokens are similar to CSRF tokens without session support, so you can
 * use them to give out URLs via protected channels and receive a callback
 * later.
 */
class StaticTokenGenerator {

  /**
   * The private key service.
   *
   * @var \Drupal\Core\PrivateKey
   */
  protected $privateKey;

  /**
   * Creates a new StaticTokenGenerator instance.
   *
   * @param \Drupal\Core\PrivateKey $privateKey
   *   The private key.
   */
  public function __construct(PrivateKey $privateKey) {
    $this->privateKey = $privateKey;
  }

  /**
   * Generates a token based on $value and the private key.
   *
   * @param string $value
   *   A value to base the token on.
   *
   * @return string
   *   A 43-character URL-safe token for validation, based on the token seed,
   *   the hash salt provided by Settings::getHashSalt(), and the
   *   'drupal_private_key' configuration variable.
   */
  public function get($value) {
    return $this->computeToken($value);
  }

  /**
   * Validates a token based on $value and the private key.
   *
   * @param string $token
   *   The token to be validated.
   * @param string $value
   *   A value to base the token on.
   *
   * @return bool
   *   TRUE for a valid token, FALSE for an invalid token.
   */
  public function validate($token, $value) {
    return $token === $this->computeToken($value);
  }

  /**
   * Generates a token based on $value and the private key.
   *
   * @param string $value
   *   A value to base the token on.
   *
   * @return string
   *   A 43-character URL-safe token for validation, based on the token seed,
   *   the hash salt provided by Settings::getHashSalt(), and the
   *   'drupal_private_key' configuration variable.
   *
   * * @see \Drupal\Core\Site\Settings::getHashSalt()
   */
  protected function computeToken($value) {
    return Crypt::hmacBase64($value, $this->privateKey->get() . Settings::getHashSalt());
  }

}
