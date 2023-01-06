<?php

declare(strict_types=1);

namespace Akeneo\Catalogs\Infrastructure\Controller\Internal;

use Akeneo\Catalogs\Application\Exception\CatalogNotFoundException;
use Akeneo\Catalogs\Application\Persistence\Catalog\GetCatalogQueryInterface;
use Akeneo\Catalogs\Application\Persistence\Catalog\Product\GetProductsWithFilteredValuesQueryInterface;
use Akeneo\Catalogs\Application\Persistence\Catalog\Product\GetProductUuidsQueryInterface;
use Akeneo\Catalogs\Domain\Catalog;
use Akeneo\Catalogs\Infrastructure\Security\GetCurrentUsernameTrait;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

/**
 * @copyright 2022 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class GetPreselectedProductsAction
{
    use GetCurrentUsernameTrait;
    public function __construct(
        private GetCatalogQueryInterface $getCatalogQuery,
        private GetProductsWithFilteredValuesQueryInterface $getProductsWithFilteredValuesQuery,
    )
    {
    }

    public function __invoke(Request $request, string $catalogId): Response
    {
        if (!$request->isXmlHttpRequest()) {
            return new RedirectResponse('/');
        }

        $search = $request->query->get('search', '');
        $productSelectionCriteria = \json_decode((string) $request->getContent(), true, 512, JSON_THROW_ON_ERROR);

        $catalog = $this->getCatalog($catalogId, $productSelectionCriteria);

        $products = $this->getProductsWithFilteredValuesQuery->execute($catalog, limit: 10);

        $products = \array_map(
            fn (array $product): array => [
                'uuid' => $product['uuid'],
                'name' => $product['values']['name'][0]['data'] ?? $product['uuid']
            ],
            $products
        );

        return new JsonResponse($products);
    }

    private function getCatalog(string $catalogId, array $productSelectionCriteria)
    {
        try {
            $catalog = $this->getCatalogQuery->execute($catalogId);
        } catch (CatalogNotFoundException) {
            throw new NotFoundHttpException(\sprintf('catalog "%s" does not exist.', $catalogId));
        }

        return new Catalog(
            $catalog->getId(),
            $catalog->getName(),
            $catalog->getOwnerUsername(),
            $catalog->isEnabled(),
            $productSelectionCriteria,
            $catalog->getProductValueFilters(),
            $catalog->getProductMapping(),
        );
    }
}
