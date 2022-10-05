<?php

declare(strict_types=1);

namespace Akeneo\Catalogs\Infrastructure\Controller\Internal;

use Akeneo\Catalogs\Application\Persistence\Catalog\FindOneCatalogByIdQueryInterface;
use Akeneo\Catalogs\Application\Persistence\Catalog\GetCatalogProductValueFiltersQueryInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

/**
 * @copyright 2022 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
final class GetCatalogAction
{
    public function __construct(
        private FindOneCatalogByIdQueryInterface $findOneCatalogByIdQuery,
        private GetCatalogProductValueFiltersQueryInterface $findCatalogProductValueFiltersQuery,
        private NormalizerInterface $normalizer,
    ) {
    }

    public function __invoke(Request $request, string $catalogId): Response
    {
        if (!$request->isXmlHttpRequest()) {
            return new RedirectResponse('/');
        }

        $catalog = $this->findOneCatalogByIdQuery->execute($catalogId);

        if (null === $catalog) {
            throw new NotFoundHttpException(\sprintf('catalog "%s" does not exist.', $catalogId));
        }

        $catalogNormalized = (array) $this->normalizer->normalize($catalog, 'internal');
        $catalogNormalized['product_value_filters'] = $this->findCatalogProductValueFiltersQuery->execute($catalogId);

        return new JsonResponse($catalogNormalized);
    }
}
