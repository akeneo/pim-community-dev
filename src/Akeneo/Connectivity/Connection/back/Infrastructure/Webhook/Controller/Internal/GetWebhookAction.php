<?php

declare(strict_types=1);

namespace Akeneo\Connectivity\Connection\Infrastructure\Webhook\Controller\Internal;

use Akeneo\Connectivity\Connection\Application\Webhook\Query\GetAConnectionWebhookHandler;
use Akeneo\Connectivity\Connection\Application\Webhook\Query\GetAConnectionWebhookQuery;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

final class GetWebhookAction
{
    public function __construct(
        private GetAConnectionWebhookHandler $getAConnectionWebhookHandler,
    ) {
    }

    public function __invoke(Request $request): JsonResponse
    {
        $eventSubscriptionFormData = $this->getAConnectionWebhookHandler->handle(
            new GetAConnectionWebhookQuery($request->get('code', '')),
        );

        if (null === $eventSubscriptionFormData) {
            return new JsonResponse(null, Response::HTTP_NOT_FOUND);
        }

        return new JsonResponse($eventSubscriptionFormData->normalize());
    }
}
