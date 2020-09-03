<?php

declare(strict_types=1);

namespace Akeneo\Connectivity\Connection\Infrastructure\Webhook;

use Akeneo\Connectivity\Connection\Domain\Webhook\Model\WebhookRequest;
use Akeneo\Connectivity\Connection\Domain\Webhook\WebhookClient;
use Akeneo\Tool\Bundle\WebhookBundle\Client\RequestFactory;
use Akeneo\Tool\Bundle\WebhookBundle\Client\RequestSender;

/**
 * @author    Thomas Galvaing <thomas.galvaing@akeneo.com>
 * @copyright 2020 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class GuzzleWebhookClient implements WebhookClient
{
    /** @var RequestFactory */
    private $requestFactory;

    /** @var RequestSender */
    private $requestSender;

    public function __construct(RequestFactory $requestFactory, RequestSender $requestSender)
    {
        $this->requestFactory = $requestFactory;
        $this->requestSender = $requestSender;
    }

    /**
     * @param WebhookRequest[] $webhookRequests
     */
    public function bulkSend(array $webhookRequests): void
    {
        $requests = [];
        foreach ($webhookRequests as $webhookRequest) {
            $webhook = $webhookRequest->webhook();
            $event = $webhookRequest->event();
            $requests[] = $this->requestFactory->create(
                $webhook->url(),
                json_encode($event->normalize()),
                ['secret' => $webhook->secret()]
            );;
        }

        $this->requestSender->send($requests);
    }
}
