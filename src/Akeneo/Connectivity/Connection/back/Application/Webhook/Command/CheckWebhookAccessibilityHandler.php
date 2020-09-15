<?php
declare(strict_types=1);

namespace Akeneo\Connectivity\Connection\Application\Webhook\Command;

use GuzzleHttp\ClientInterface;
use GuzzleHttp\Exception\GuzzleException;
use GuzzleHttp\Exception\RequestException;
use GuzzleHttp\Psr7\Request;

/**
 * @author    Thomas Galvaing <thomas.galvaing@akeneo.com>
 * @copyright 2020 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
final class CheckWebhookAccessibilityHandler
{
    /** @var string */
    const POST = 'POST';

    /** @var ClientInterface */
    private $client;

    public function __construct(ClientInterface $client)
    {
        $this->client = $client;
    }

    /**
     * @param CheckWebhookAccessibilityCommand $command
     * @return array
     */
    public function handle(CheckWebhookAccessibilityCommand $command): array
    {
        if (!$this->checkUrlFormat($command->webhookUrl())) {
            return [
                'success' => 'false',
                'message' => 'akeneo_connectivity.connection.webhook.constraint.url.invalid_format',
            ];
        }

        $checkResponse = [
            'success' => 'false',
            'message' => 'Failed to connect to server',
        ];

        try {

            $response = $this->client->send(new Request(self::POST, $command->webhookUrl()));

            $checkResponse['success'] = 'true';
            $checkResponse['message'] = $response->getReasonPhrase();
            $checkResponse['code'] = $response->getStatusCode();

        } catch (GuzzleException $e) {
            if ($e instanceof RequestException && $e->hasResponse()) {
                $checkResponse['code'] = $e->getResponse()->getStatusCode();
                $checkResponse['message'] = $e->getResponse()->getReasonPhrase();
            }
        }

        return $checkResponse;
    }

    /**
     * @param string $url
     * @return bool
     */
    private function checkUrlFormat(string $url): bool
    {
        return !(false === filter_var($url, FILTER_VALIDATE_URL));
    }
}

