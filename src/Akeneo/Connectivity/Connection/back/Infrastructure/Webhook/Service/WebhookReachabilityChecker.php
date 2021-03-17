<?php

declare(strict_types=1);

namespace Akeneo\Connectivity\Connection\Infrastructure\Webhook\Service;

use Akeneo\Connectivity\Connection\Application\Webhook\Service\UrlReachabilityCheckerInterface;
use Akeneo\Connectivity\Connection\Application\Webhook\Validation\NotPrivateNetworkUrl;
use Akeneo\Connectivity\Connection\Domain\Clock;
use Akeneo\Connectivity\Connection\Domain\Webhook\DTO\UrlReachabilityStatus;
use Akeneo\Connectivity\Connection\Infrastructure\Webhook\Client\Signature;
use Akeneo\Connectivity\Connection\Infrastructure\Webhook\RequestHeaders;
use GuzzleHttp\ClientInterface;
use GuzzleHttp\Exception\GuzzleException;
use GuzzleHttp\Exception\RequestException;
use GuzzleHttp\Psr7\Request;
use Psr\Http\Message\ResponseInterface;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Validator\Validator\ValidatorInterface;

/**
 * @author    Thomas Galvaing <thomas.galvaing@akeneo.com>
 * @copyright 2020 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class WebhookReachabilityChecker implements UrlReachabilityCheckerInterface
{
    /** @var string */
    const POST = 'POST';

    /** @var string */
    const CONNECTION_FAILED = 'Failed to connect to server';

    /** @var ClientInterface */
    private $client;

    /** @var ValidatorInterface */
    private $validator;

    private Clock $clock;

    public function __construct(
        ClientInterface $client,
        ValidatorInterface $validator,
        Clock $clock
    ) {
        $this->client = $client;
        $this->validator = $validator;
        $this->clock = $clock;
    }

    public function check(string $url, string $secret): UrlReachabilityStatus
    {
        $violations = $this->validator->validate($url, [
            new Assert\NotBlank(),
            new Assert\Url(),
            new NotPrivateNetworkUrl()
        ]);

        if (0 !== count($violations)) {
            return new UrlReachabilityStatus(
                false,
                $violations->get(0)->getMessage()
            );
        }

        $timestamp = $this->clock->now()->getTimestamp();
        $signature = Signature::createSignature($secret, $timestamp);

        $headers = [
            'Content-Type' => 'application/json',
            RequestHeaders::HEADER_REQUEST_SIGNATURE => $signature,
            RequestHeaders::HEADER_REQUEST_TIMESTAMP => $timestamp,
        ];

        try {
            $response = $this->client->send(new Request(self::POST, $url, $headers), [
                'allow_redirects' => false /* Block http redirect to limit security risks (SSRF) */,
            ]);

            $statusCode = $response->getStatusCode();

            // Treat redirection as error.
            if ($statusCode >= 300 and $statusCode < 400) {
                return new UrlReachabilityStatus(
                    false,
                    sprintf(
                        '%s %s. Redirection are not allowed.',
                        $statusCode,
                        $response->getReasonPhrase()
                    )
                );
            }

            return new UrlReachabilityStatus(
                true,
                sprintf("%s %s", $statusCode, $response->getReasonPhrase())
            );
        } catch (GuzzleException $e) {
            if ($e instanceof RequestException && $e->hasResponse()) {

                /** @var ResponseInterface */
                $response = $e->getResponse();

                return new UrlReachabilityStatus(
                    false,
                    sprintf("%s %s", $response->getStatusCode(), $response->getReasonPhrase())
                );
            } else {
                return new UrlReachabilityStatus(
                    false,
                    self::CONNECTION_FAILED
                );
            }
        }
    }
}
