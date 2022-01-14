<?php

namespace Akeneo\Platform\Syndication\Infrastructure\Controller\ExternalApi;

use Akeneo\Platform\Syndication\Application\Command\UpdatePlatformCommand;
use Akeneo\Platform\Syndication\Application\Command\UpdatePlatformHandler;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;

class UpdatePlatformAction
{
    private UpdatePlatformHandler $updatePlatformCommandHandler;

    public function __construct(
        UpdatePlatformHandler $updatePlatformCommandHandler
    ) {
        $this->updatePlatformCommandHandler = $updatePlatformCommandHandler;
    }

    public function __invoke(Request $request, string $code): JsonResponse
    {
        $normalizedPlatform = $this->getNormalizedPlatformFromRequest($request);

        $updatePlatformCommand = new UpdatePlatformCommand();
        $updatePlatformCommand->code = $code;
        $updatePlatformCommand->label = $normalizedPlatform['label'];
        $updatePlatformCommand->enabled = $normalizedPlatform['enabled'] ?? true;
        $updatePlatformCommand->families = $normalizedPlatform['families'] ?? [];

        $this->updatePlatformCommandHandler->handle($updatePlatformCommand);

        return new JsonResponse(null, 204);
    }

    private function getNormalizedPlatformFromRequest(Request $request): array
    {
        $normalizedPlatform = json_decode($request->getContent(), true);

        if (null === $normalizedPlatform) {
            throw new BadRequestHttpException('Invalid json message received');
        }

        return $normalizedPlatform;
    }
}
