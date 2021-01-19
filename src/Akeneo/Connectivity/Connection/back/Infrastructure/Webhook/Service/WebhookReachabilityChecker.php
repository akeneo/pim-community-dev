<?php
declare(strict_types=1);

namespace Akeneo\Connectivity\Connection\Infrastructure\Webhook\Service;

use Akeneo\Connectivity\Connection\Application\Webhook\Service\UrlReachabilityCheckerInterface;
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

    public function __construct(ClientInterface $client, ValidatorInterface $validator)
    {
        $this->client = $client;
        $this->validator = $validator;
    }

    public function check(string $url, string $secret): UrlReachabilityStatus
    {
        $violations = $this->validator->validate($url, [new Assert\Url(), new Assert\NotBlank(),]);

        if (0 !== count($violations)) {
            return new UrlReachabilityStatus(
                false,
                $violations->get(0)->getMessage()
            );
        }

        $timestamp = time();
        $signature = Signature::createSignature($secret, $timestamp);

        $headers = [
            'Content-Type' => 'application/json',
            RequestHeaders::HEADER_REQUEST_SIGNATURE => $signature,
            RequestHeaders::HEADER_REQUEST_TIMESTAMP => $timestamp,
        ];

        try {
            $response = $this->client->send(new Request(self::POST, $url, $headers));

            return new UrlReachabilityStatus(
                true,
                sprintf("%s %s", $response->getStatusCode(), $response->getReasonPhrase())
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
