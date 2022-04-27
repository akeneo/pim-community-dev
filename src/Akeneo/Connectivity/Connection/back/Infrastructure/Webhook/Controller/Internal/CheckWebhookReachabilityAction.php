<?php

declare(strict_types=1);

namespace Akeneo\Connectivity\Connection\Infrastructure\Webhook\Controller\Internal;

use Akeneo\Connectivity\Connection\Application\Webhook\Command\CheckWebhookReachabilityCommand;
use Akeneo\Connectivity\Connection\Application\Webhook\Command\CheckWebhookReachabilityHandler;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;

final class CheckWebhookReachabilityAction
{
    public function __construct(
        private CheckWebhookReachabilityHandler $checkWebhookReachabilityHandler,
    ) {
    }

    public function __invoke(Request $request): JsonResponse
    {
        $url = $request->get('url', '');
        $secret = $request->get('secret', '');
        $checkWebhookReachability = $this->checkWebhookReachabilityHandler->handle(
            new CheckWebhookReachabilityCommand($url, $secret)
        );

        return new JsonResponse($checkWebhookReachability->normalize());
    }
}
