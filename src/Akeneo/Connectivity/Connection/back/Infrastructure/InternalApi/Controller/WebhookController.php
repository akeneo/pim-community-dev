<?php
declare(strict_types=1);

namespace Akeneo\Connectivity\Connection\Infrastructure\InternalApi\Controller;

use Akeneo\Connectivity\Connection\Application\Webhook\Command\CheckWebhookAccessibilityCommand;
use Akeneo\Connectivity\Connection\Application\Webhook\Command\CheckWebhookAccessibilityHandler;
use Akeneo\Connectivity\Connection\Application\Webhook\Query\GetAConnectionWebhookHandler;
use Akeneo\Connectivity\Connection\Application\Webhook\Query\GetAConnectionWebhookQuery;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class WebhookController
{
    /** @var GetAConnectionWebhookHandler */
    private $getAConnectionWebhookHandler;

    /** @var CheckWebhookAccessibilityHandler */
    private $checkWebhookAccessibilityHandler;

    public function __construct(
        GetAConnectionWebhookHandler $getAConnectionWebhookHandler,
        CheckWebhookAccessibilityHandler $checkWebhookAccessibilityHandler
    ) {
        $this->getAConnectionWebhookHandler = $getAConnectionWebhookHandler;
        $this->checkWebhookAccessibilityHandler = $checkWebhookAccessibilityHandler;
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

    public function checkWebhookAccessibility(Request $request): JsonResponse
    {
        $url = $request->get('url', '');
        $checkWebhookAccessibility = $this->checkWebhookAccessibilityHandler->handle(
            new CheckWebhookAccessibilityCommand($url)
        );

        return new JsonResponse($checkWebhookAccessibility);
    }
}
