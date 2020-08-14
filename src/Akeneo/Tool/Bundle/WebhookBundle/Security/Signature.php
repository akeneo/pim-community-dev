<?php

namespace Akeneo\Tool\Bundle\WebhookBundle\Security;

/**
 * @copyright 2020 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class Signature
{
    public static function create(string $secret, string $body, int $timestamp): string
    {
        $data = (string)$timestamp . '.' . $body;

        return hash_hmac('sha256', $data, $secret);
    }

    public static function verify(string $originalSignature, string $generatedSignature): bool
    {
        return hash_equals($originalSignature, $generatedSignature);
    }
}
