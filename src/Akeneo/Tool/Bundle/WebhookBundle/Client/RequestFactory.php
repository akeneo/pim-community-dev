<?php

namespace Akeneo\Tool\Bundle\WebhookBundle\Client;

use Akeneo\Tool\Bundle\WebhookBundle\Security\Signature;
use GuzzleHttp\Psr7\Request;

/**
 * @copyright 2020 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class RequestFactory
{
    /**
     * Create a Psr7\Request with the Timestamp & Signature headers for security purpose.
     *
     * @param string $url URL of the webhook expecting a POST request.
     * @param string $body Body of the request. Expected format is JSON but Content-Type header can be changed.
     * @param array{secret: string, ?headers: array<string, string>} $options The "secret" option is mandatory.
     */
    public static function create(string $url, string $body, array $options): Request
    {
        if (null === $secret = $options['secret']) {
            throw new \InvalidArgumentException('The "secret" option is missing.');
        }

        $timestamp = time();
        $signature = Signature::create($secret, $body, $timestamp);

        $headers = array_merge(
            [
                'Content-Type' => 'application/json',
                'X-Akeneo-Timestamp' => $timestamp,
                'X-Akeneo-Signature' => $signature,
            ],
            $options['headers'] ?? []
        );

        $request = new Request('POST', $url, $headers, $body);

        return $request;
    }
}
