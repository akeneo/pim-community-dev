<?php

declare(strict_types=1);

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2022 Akeneo SAS (https://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Akeneo\Platform\JobAutomation\Infrastructure\Security;

class Encrypter
{
    public function __construct(
        private readonly string $appSecret,
    ) {
    }

    public function encrypt(string $valueToEncrypt, string $key): string
    {
        $key = $this->getKey($key);

        $encryptionKey = \hash_hkdf('sha256', $key, 0, 'aes-256-encryption');
        $authenticationKey = \hash_hkdf('sha256', $key, 0, 'sha-256-authentication');

        $iv = \openssl_random_pseudo_bytes(\openssl_cipher_iv_length('aes-256-cbc') ?: 0);
        $cipherText = \openssl_encrypt($valueToEncrypt, 'aes-256-cbc', $encryptionKey, 0, $iv);
        $hmac = \hash_hmac('sha256', $iv.$cipherText, $authenticationKey);

        return \base64_encode($iv.$hmac.$cipherText);
    }

    public function decrypt(string $valueEncrypted, string $key): string
    {
        $key = $this->getKey($key);

        $triplet = \base64_decode($valueEncrypted);

        $encryptionKey = \hash_hkdf('sha256', $key, 0, 'aes-256-encryption');
        $authenticationKey = \hash_hkdf('sha256', $key, 0, 'sha-256-authentication');

        $ivlen = \openssl_cipher_iv_length('aes-256-cbc') ?: 0;
        $iv = \substr($triplet, 0, $ivlen);
        $hmac = \substr($triplet, $ivlen, 64);
        $cipherText = \substr($triplet, $ivlen + 64);

        $compare = \hash_hmac('sha256', $iv.$cipherText, $authenticationKey);

        if ($hmac !== $compare) {
            throw new \Exception('invalid hmac', 401);
        }

        $clearText = \openssl_decrypt($cipherText, 'aes-256-cbc', $encryptionKey, 0, $iv);

        return $clearText ?: '';
    }

    private function getKey(string $key): string
    {
        return sprintf('%s-%s', $key, $this->appSecret);
    }
}
