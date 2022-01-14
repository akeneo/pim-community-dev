<?php

namespace Akeneo\Platform\Syndication\Infrastructure\Controller\ExternalApi;

use Akeneo\Platform\Syndication\Application\Command\UpdatePlatformFamilyCommand;
use Akeneo\Platform\Syndication\Application\Command\UpdatePlatformFamilyHandler;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;

class UpdatePlatformFamilyAction
{
    private UpdatePlatformFamilyHandler $updatePlatformFamilyCommandHandler;

    public function __construct(
        UpdatePlatformFamilyHandler $updatePlatformFamilyCommandHandler
    ) {
        $this->updatePlatformFamilyCommandHandler = $updatePlatformFamilyCommandHandler;
    }

    public function __invoke(Request $request, string $platformCode, string $code): JsonResponse
    {
        $normalizedPlatformFamily = $this->getNormalizedPlatformFamilyFromRequest($request);

        $updatePlatformFamilyCommand = new UpdatePlatformFamilyCommand();
        $updatePlatformFamilyCommand->code = $code;
        $updatePlatformFamilyCommand->platformCode = $platformCode;
        $updatePlatformFamilyCommand->label = $normalizedPlatformFamily['label'];
        $updatePlatformFamilyCommand->requirements = $normalizedPlatformFamily['requirements'] ?? [];

        $this->updatePlatformFamilyCommandHandler->handle($updatePlatformFamilyCommand);

        return new JsonResponse(null, 204);
    }

    private function getNormalizedPlatformFamilyFromRequest(Request $request): array
    {
        $normalizedPlatformFamily = json_decode($request->getContent(), true);

        if (null === $normalizedPlatformFamily) {
            throw new BadRequestHttpException('Invalid json message received');
        }

        return $normalizedPlatformFamily;
    }
}
