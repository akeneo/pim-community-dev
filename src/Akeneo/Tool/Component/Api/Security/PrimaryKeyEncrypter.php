<?php

namespace Akeneo\Tool\Component\Api\Security;

/**
 * Primary key encrypter allows to encrypt or decrypt a primary key in order to not expose database identifier
 * to the client of the API.
 *
 * @author    Alexandre Hocquard <alexandre.hocquard@akeneo.com>
 * @copyright 2017 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class PrimaryKeyEncrypter
{
    /** @var string */
    protected $key;

    /** @var string */
    protected $method;

    /** @var string */
    protected $initializationVector;

    /**
     * @param string $method               method to encrypt/decrypt data
     * @param string $key                  key to encrypt/decrypt data
     * @param string $initializationVector secret added to the key to encrypt/decrypt data,
     *                                     it will be truncated at 16 bytes if too long and left padded with 0
     */
    public function __construct($method, $key, $initializationVector)
    {
        $this->method = $method;
        $this->key = $key;
        $this->initializationVector = str_pad(substr($initializationVector, 0, 16), 16, "0", STR_PAD_LEFT);
    }

    /**
     * Encrypt the primary key.
     * This is a very basic security implementation, as the initialization vector is static.
     *
     * @param string $primaryKey
     *
     * @return string
     */
    public function encrypt($primaryKey)
    {
        return openssl_encrypt($primaryKey, $this->method, $this->key, 0, $this->initializationVector);
    }

    /**
     * Decrypt the primary key.
     *
     * @param string $primaryKey
     *
     * @return string
     */
    public function decrypt($primaryKey)
    {
        return openssl_decrypt($primaryKey, $this->method, $this->key, 0, $this->initializationVector);
    }
}
