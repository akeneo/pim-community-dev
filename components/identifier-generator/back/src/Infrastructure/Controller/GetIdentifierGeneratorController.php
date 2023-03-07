<?php

declare(strict_types=1);

namespace Akeneo\Pim\Automation\IdentifierGenerator\Infrastructure\Controller;

use Akeneo\Pim\Automation\IdentifierGenerator\Domain\Repository\IdentifierGeneratorRepository;
use Akeneo\Platform\Bundle\FrameworkBundle\Security\SecurityFacadeInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;

/**
 * @copyright 2022 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
final class GetIdentifierGeneratorController
{
    public function __construct(
        private readonly IdentifierGeneratorRepository $identifierGeneratorRepository,
        private readonly SecurityFacadeInterface $security,
    ) {
    }

    public function __invoke(Request $request, string $code): Response
    {
        if (!$request->isXmlHttpRequest()) {
            return new RedirectResponse('/');
        }
        if (!$this->security->isGranted('pim_identifier_generator_view')
            && !$this->security->isGranted('pim_identifier_generator_manage')
        ) {
            throw new AccessDeniedException();
        }

        $identifierGenerator = $this->identifierGeneratorRepository->get($code);
        if (!$identifierGenerator) {
            return new JsonResponse(\sprintf("Identifier generator \"%s\" does not exist or you do not have permission to access it.", $code), Response::HTTP_NOT_FOUND);
        }

        return new JsonResponse($identifierGenerator->normalize());
    }
}
