<?php

declare(strict_types=1);

namespace Akeneo\Connectivity\Connection\Infrastructure\Webhook\Controller\Internal;

use Akeneo\Connectivity\Connection\Application\Webhook\Command\GenerateWebhookSecretCommand;
use Akeneo\Connectivity\Connection\Application\Webhook\Command\GenerateWebhookSecretHandler;
use Akeneo\Connectivity\Connection\Domain\Webhook\Exception\ConnectionWebhookNotFoundException;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

final class RegenerateSecretAction
{
    public function __construct(
        private GenerateWebhookSecretHandler $generateWebhookSecretHandler,
    ) {
    }

    public function __invoke(Request $request): JsonResponse
    {
        try {
            $this->generateWebhookSecretHandler->handle(
                new GenerateWebhookSecretCommand($request->get('code', ''))
            );

            return new JsonResponse(null, Response::HTTP_NO_CONTENT);
        } catch (ConnectionWebhookNotFoundException $notFoundException) {
            return new JsonResponse($notFoundException->getMessage(), Response::HTTP_NOT_FOUND);
        }
    }
}
