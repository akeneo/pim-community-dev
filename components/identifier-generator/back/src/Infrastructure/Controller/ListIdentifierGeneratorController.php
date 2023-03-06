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
 * @copyright 2022 Akeneo SAS (https://www.akeneo.com)
 * @license   https://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
final class ListIdentifierGeneratorController
{
    public function __construct(
        private readonly IdentifierGeneratorRepository $identifierGeneratorRepository,
        private readonly SecurityFacadeInterface $security,
    ) {
    }

    public function __invoke(Request $request): Response
    {
        if (!$request->isXmlHttpRequest()) {
            return new RedirectResponse('/');
        }
        if (!$this->security->isGranted('pim_identifier_generator_view')
            && !$this->security->isGranted('pim_identifier_generator_manage')
        ) {
            throw new AccessDeniedException();
        }

        $identifiersGenerators = $this->identifierGeneratorRepository->getAll();
        $result = \array_map(fn ($identifierGenerator) => $identifierGenerator->normalize(), $identifiersGenerators);

        return new JsonResponse($result, Response::HTTP_OK);
    }
}
