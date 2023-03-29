<?php

declare(strict_types=1);

namespace Akeneo\Pim\Automation\IdentifierGenerator\Infrastructure\Controller;

use Akeneo\Pim\Automation\IdentifierGenerator\Application\Delete\DeleteGeneratorCommand;
use Akeneo\Pim\Automation\IdentifierGenerator\Application\Delete\DeleteGeneratorHandler;
use Akeneo\Pim\Automation\IdentifierGenerator\Domain\Exception\CouldNotFindIdentifierGeneratorException;
use Akeneo\Platform\Bundle\FrameworkBundle\Security\SecurityFacadeInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;

/**
 * @copyright 2022 Akeneo SAS (https://www.akeneo.com)
 * @license   https://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
final class DeleteIdentifierGeneratorController
{
    public function __construct(
        private readonly DeleteGeneratorHandler $deleteGeneratorHandler,
        private readonly SecurityFacadeInterface $security,
    ) {
    }

    public function __invoke(Request $request, string $code): Response
    {
        if (!$request->isXmlHttpRequest()) {
            return new RedirectResponse('/');
        }
        if (!$this->security->isGranted('pim_identifier_generator_manage')) {
            throw new AccessDeniedException();
        }

        try {
            ($this->deleteGeneratorHandler)(DeleteGeneratorCommand::fromCode($code));
        } catch (CouldNotFindIdentifierGeneratorException) {
            return new JsonResponse(
                \sprintf('Identifier generator "%s" does not exist or you do not have permission to access it.', $code),
                Response::HTTP_NOT_FOUND
            );
        }

        return new JsonResponse([], Response::HTTP_ACCEPTED);
    }
}
