<?php

declare(strict_types=1);

namespace Akeneo\Category\Infrastructure\Controller\InternalApi;

use Akeneo\Category\Api\Command\CommandMessageBus;
use Akeneo\Category\Application\Command\DeactivateTemplateCommand;
use Akeneo\Category\Infrastructure\FileSystem\DeleteFilesFromStorage;
use Akeneo\Tool\Component\FileStorage\FilesystemProvider;
use Oro\Bundle\SecurityBundle\SecurityFacade;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;

/**
 * @copyright 2023 Akeneo SAS (https://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class YoloController
{
    public function __construct(
        private readonly DeleteFilesFromStorage $deleteImageFromStorage,
    ) {
    }

    public function __invoke(Request $request): Response
    {
        $this->deleteImageFromStorage->delete();

        return new Response(null, Response::HTTP_ACCEPTED);
    }
}
