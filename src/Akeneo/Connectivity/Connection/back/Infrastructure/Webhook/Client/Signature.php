<?php

namespace Akeneo\Connectivity\Connection\Infrastructure\Webhook\Client;

/**
 * @copyright 2020 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class Signature
{
    public static function createSignature(string $secret, int $timestamp, ?string $body = null): string
    {
        $data = (string)$timestamp . '.' . $body;

        return hash_hmac('sha256', $data, $secret);
    }
}
