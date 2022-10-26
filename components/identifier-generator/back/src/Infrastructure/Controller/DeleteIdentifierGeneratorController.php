<?php

declare(strict_types=1);

namespace Akeneo\Pim\Automation\IdentifierGenerator\Infrastructure\Controller;

use Akeneo\Pim\Automation\IdentifierGenerator\Domain\Repository\IdentifierGeneratorRepository;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

/**
 * @copyright 2022 Akeneo SAS (https://www.akeneo.com)
 * @license   https://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
final class DeleteIdentifierGeneratorController
{
    public function __construct(
        private IdentifierGeneratorRepository $identifierGeneratorRepository,
    ) {
    }

    public function __invoke(Request $request): Response
    {
        if (!$request->isXmlHttpRequest()) {
            return new RedirectResponse('/');
        }

        $identifierGeneratorCode = strval($request->get('code'));

        $identifierGenerator = $this->identifierGeneratorRepository->get($identifierGeneratorCode);
        if (!$identifierGenerator) {
            throw new NotFoundHttpException(sprintf('%s identifier generator not found', $identifierGeneratorCode));
        }

        $this->identifierGeneratorRepository->delete($identifierGeneratorCode);

        return new JsonResponse([], Response::HTTP_ACCEPTED);
    }
}
