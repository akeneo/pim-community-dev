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

namespace Akeneo\Platform\Component\Tenant;

use Webmozart\Assert\Assert;

final class TenantContextDecoder implements TenantContextDecoderInterface
{
    private const OPEN_SSL_CIPHER_METHOD = 'aes-256-cbc';

    public function __construct(private string $encodedEncryptionKey)
    {
    }

    /**
     * A lots of try/catch blocks to not propagate exceptions with potential security leaks in the messages.
     * We catch them to replace by a more generic message
     */
    public function decode(string $encodedValues): array
    {
        $encodePayload = \json_decode($encodedValues, true);

        try {
            $data = \base64_decode($encodePayload['data']);
            $iv = \hex2bin($encodePayload['iv']);
        } catch (\Throwable $e) {
            throw new TenantContextDecoderException(
                sprintf('Unable to decode tenant values payload: %s', $e->getMessage())
            );
        }

        try {
            $key = $this->decodeEncryptionKey();
        } catch (\Throwable $e) {
            throw new TenantContextDecoderException(
                sprintf('Unable to decode encryption key: %s', $e->getMessage())
            );
        }

        try {
            $decrypted = \openssl_decrypt(
                data: $data,
                cipher_algo: self::OPEN_SSL_CIPHER_METHOD,
                passphrase: $key,
                options: OPENSSL_RAW_DATA,
                iv: $iv
            );
        } catch (\Throwable $e) {
            throw new TenantContextDecoderException(
                sprintf('Unable to decrypt tenant values: %s', $e->getMessage())
            );
        }

        if (false === $decrypted) {
            throw new TenantContextDecoderException('Unable to decrypt tenant values.');
        }

        try {
            $decoded = \json_decode(
                json: $decrypted,
                associative: true,
                flags: JSON_THROW_ON_ERROR
            );
        } catch (\Throwable $e) {
            throw new TenantContextDecoderException('Decrypted values is not a valid json string.');
        }

        try {
            Assert::isMap($decoded, 'Tenant values are not a valid map.');
        } catch (\Throwable $e) {
            throw new TenantContextDecoderException('Tenant values is not a valid json map.');
        }

        return $decoded;
    }

    private function decodeEncryptionKey(): string
    {
        return \base64_decode($this->encodedEncryptionKey);
    }
}
