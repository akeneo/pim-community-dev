<?php
declare(strict_types=1);

namespace Akeneo\Connectivity\Connection\Infrastructure\Webhook\Service;

use Akeneo\Connectivity\Connection\Application\Webhook\Service\UrlReachabilityCheckerInterface;
use Akeneo\Connectivity\Connection\Domain\Webhook\DTO\UrlReachabilityStatus;
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
    const WRONG_URL = 'akeneo_connectivity.connection.webhook.error.wrong_url';

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

    /**
     * @param string $url
     * @return UrlReachabilityStatus
     */
    public function check(string $url): UrlReachabilityStatus
    {
        if (0 !== count($this->validator->validate($url, [new Assert\Url(), new Assert\NotBlank(),]))) {

            return new UrlReachabilityStatus(
                false,
                self::WRONG_URL
            );
        }

        try {
            $response = $this->client->send(new Request(self::POST, $url));

            return new UrlReachabilityStatus(
                true,
                $this->buildMessage($response->getReasonPhrase(), $response->getStatusCode())
            );
        } catch (GuzzleException $e) {
            if ($e instanceof RequestException && $e->hasResponse()) {

                /** @var ResponseInterface */
                $response = $e->getResponse();

                return new UrlReachabilityStatus(
                    false,
                    $this->buildMessage($response->getReasonPhrase(), $response->getStatusCode())
                );
            } else {
                return new UrlReachabilityStatus(
                    false,
                    $this->buildMessage(self::CONNECTION_FAILED)
                );
            }
        }
    }

    /**
     * @param string $reasonPhrase
     * @param int|null $code
     * @return string
     */
    private function buildMessage(string $reasonPhrase, int $code = null): string
    {
        return $code ? sprintf("%s %s", $code, $reasonPhrase) : $reasonPhrase;
    }
}
