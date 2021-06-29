<?php

declare(strict_types=1);

namespace Akeneo\Connectivity\Connection\Infrastructure\Service;

/**
 * Encrypter allows to encrypt or decrypt a string in order to not expose it externally.
 *
 * @copyright 2021 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class Encrypter
{
    private string $key;
    private string $method;
    private string $initializationVector;

    /**
     * @param string $method method to encrypt/decrypt data
     * @param string $key key to encrypt/decrypt data
     * @param string $initializationVector secret added to the key to encrypt/decrypt data,
     *                                     it will be truncated at 16 bytes if too long and left padded with 0
     */
    public function __construct(
        string $method,
        string $key,
        string $initializationVector
    ) {
        $this->method = $method;
        $this->key = $key;
        $this->initializationVector = str_pad(substr($initializationVector, 0, 16), 16, "0", STR_PAD_LEFT);
    }

    /**
     * This is a very basic security implementation, as the initialization vector is static.
     */
    public function encrypt(string $value): string
    {
        return openssl_encrypt($value, $this->method, $this->key, 0, $this->initializationVector);
    }

    public function decrypt(string $value): string
    {
        return openssl_decrypt($value, $this->method, $this->key, 0, $this->initializationVector);
    }
}
