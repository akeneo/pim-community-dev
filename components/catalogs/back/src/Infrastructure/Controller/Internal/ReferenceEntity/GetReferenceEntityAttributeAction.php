<?php

declare(strict_types=1);

namespace Akeneo\Catalogs\Infrastructure\Controller\Internal\ReferenceEntity;

use Akeneo\Catalogs\Application\Persistence\ReferenceEntity\FindOneReferenceEntityAttributeByIdentifierQueryInterface;
use Akeneo\Catalogs\Infrastructure\Persistence\ReferenceEntity\FindOneReferenceEntityAttributeByIdentifierQuery;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

/**
 * @copyright 2023 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class GetReferenceEntityAttributeAction
{
    public function __construct(
        private readonly FindOneReferenceEntityAttributeByIdentifierQueryInterface $findOneReferenceEntityAttributeByIdentifierQuery,
    ) {
    }

    public function __invoke(Request $request, string $identifier): Response
    {
        if (!$request->isXmlHttpRequest()) {
            return new RedirectResponse('/');
        }

        // todo : get PIM current locale
        $referenceEntityAttribute = $this->findOneReferenceEntityAttributeByIdentifierQuery->execute($identifier, 'en_US');

        if (null === $referenceEntityAttribute) {
            throw new NotFoundHttpException(\sprintf('Reference Entity attribute "%s" does not exist.', $identifier));
        }

        return new JsonResponse($referenceEntityAttribute);
    }
}
