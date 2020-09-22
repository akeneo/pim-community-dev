<?php
declare(strict_types=1);

namespace Akeneo\Connectivity\Connection\Infrastructure\InternalApi\Controller;

use Akeneo\Connectivity\Connection\Application\Webhook\Command\CheckWebhookReachabilityCommand;
use Akeneo\Connectivity\Connection\Application\Webhook\Command\CheckWebhookReachabilityHandler;
use Akeneo\Connectivity\Connection\Application\Webhook\Query\GetAConnectionWebhookHandler;
use Akeneo\Connectivity\Connection\Application\Webhook\Query\GetAConnectionWebhookQuery;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class WebhookController
{
    /** @var GetAConnectionWebhookHandler */
    private $getAConnectionWebhookHandler;

    /** @var CheckWebhookReachabilityHandler */
    private $checkWebhookReachabilityHandler;

    public function __construct(
        GetAConnectionWebhookHandler $getAConnectionWebhookHandler,
        CheckWebhookReachabilityHandler $checkWebhookReachabilityHandler
    ) {
        $this->getAConnectionWebhookHandler = $getAConnectionWebhookHandler;
        $this->checkWebhookReachabilityHandler = $checkWebhookReachabilityHandler;
    }

    public function get(Request $request): JsonResponse
    {
        $connectionWebhook = $this->getAConnectionWebhookHandler->handle(
            new GetAConnectionWebhookQuery($request->get('code', ''))
        );

        if (null === $connectionWebhook) {
            return new JsonResponse(null, Response::HTTP_NOT_FOUND);
        }

        return new JsonResponse($connectionWebhook->normalize());
    }

    public function checkWebhookReachability(Request $request): JsonResponse
    {
        $url = $request->get('url', '');
        $checkWebhookReachability = $this->checkWebhookReachabilityHandler->handle(
            new CheckWebhookReachabilityCommand($url)
        );

        return new JsonResponse($checkWebhookReachability->normalize());
    }
}
