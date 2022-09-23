<?php

declare(strict_types=1);

namespace Akeneo\Catalogs\Infrastructure\Controller\Internal;

use Akeneo\Catalogs\Application\Persistence\Catalog\GetCatalogsByOwnerUsernameQueryInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

/**
 * @copyright 2022 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
final class GetAllCatalogsByOwnerAction
{
    private const CATALOG_LIMIT = 15;

    public function __construct(
        private GetCatalogsByOwnerUsernameQueryInterface $getCatalogsByOwnerUsernameQuery,
        private NormalizerInterface $normalizer,
    ) {
    }

    public function __invoke(Request $request): Response
    {
        if (!$request->isXmlHttpRequest()) {
            return new RedirectResponse('/');
        }

        $ownerName = $request->query->get('owner');

        if (!\is_string($ownerName) || '' === $ownerName) {
            throw new BadRequestHttpException('owner must not be empty.');
        }

        $catalogs = $this->getCatalogsByOwnerUsernameQuery->execute($ownerName, 0, self::CATALOG_LIMIT);

        return new JsonResponse($this->normalizer->normalize($catalogs, 'internal'));
    }
}
