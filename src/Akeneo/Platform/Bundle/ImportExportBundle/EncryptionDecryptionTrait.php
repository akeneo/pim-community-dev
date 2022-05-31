<?php

namespace Akeneo\Platform\Bundle\ImportExportBundle;

trait EncryptionDecryptionTrait
{
    public function encrypt(string $valueToEncrypt, string $key): string
    {
        $encryptionKey = \hash_hkdf('sha256', $key, 0, 'aes-256-encryption');
        $authenticationKey = \hash_hkdf('sha256', $key, 0, 'sha-256-authentication');

        $iv = \openssl_random_pseudo_bytes(\openssl_cipher_iv_length('aes-256-cbc') ?: 0);
        $cipherText = \openssl_encrypt($valueToEncrypt, 'aes-256-cbc', $encryptionKey, 0, $iv);
        $hmac = \hash_hmac('sha256', $iv.$cipherText, $authenticationKey);

        return \base64_encode($iv.$hmac.$cipherText);
    }

    public function decrypt(string $valueEncrypted, string $key): string
    {
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
}
