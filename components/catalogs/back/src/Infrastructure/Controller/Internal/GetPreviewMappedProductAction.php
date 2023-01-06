<?php

declare(strict_types=1);

namespace Akeneo\Catalogs\Infrastructure\Controller\Internal;

use Akeneo\Catalogs\Application\Persistence\Catalog\GetCatalogQueryInterface;
use Akeneo\Catalogs\Application\Persistence\Catalog\Product\GetRawProductQueryInterface;
use Akeneo\Catalogs\Application\Persistence\ProductMappingSchema\GetProductMappingSchemaQueryInterface;
use Akeneo\Catalogs\Application\Service\ProductMapperInterface;
use Akeneo\Catalogs\Domain\Catalog;
use Akeneo\Catalogs\ServiceAPI\Query\GetMappedProductsQuery;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Messenger\Exception\ValidationFailedException;

/**
 * @copyright 2022 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 *
 * @phpstan-import-type MappedProduct from GetMappedProductsQuery
 */
final class GetPreviewMappedProductAction
{
    public function __construct(
        private GetCatalogQueryInterface $getCatalogQuery,
        private GetRawProductQueryInterface $getRawProductQuery,
        private ProductMapperInterface $productMapper,
        private GetProductMappingSchemaQueryInterface $getProductMappingSchemaQuery,
    ) {
    }

    public function __invoke(Request $request, string $catalogId, string $productUuid): Response
    {
        if (!$request->isXmlHttpRequest()) {
            return new RedirectResponse('/');
        }

        $catalog = $this->getCatalog($catalogId, $productUuid);

        $product = $this->getRawProductQuery->execute($productUuid);

        if (null === $product) {
            throw new NotFoundHttpException('todo');
        }

        $productMappingSchema = $this->getProductMappingSchemaQuery->execute($catalog->getId());
        $productMapping = \json_decode((string) $request->getContent(), true, 512, JSON_THROW_ON_ERROR);

        $mappedProduct = $this->productMapper->getMappedProduct($product, $productMappingSchema, $productMapping);

        return new JsonResponse($mappedProduct, Response::HTTP_OK);
    }

    private function getCatalog(string $id, string $productUuid): Catalog
    {
        try {
            $catalog = $this->getCatalogQuery->execute($id);
        } catch (ValidationFailedException $e) {
            throw new NotFoundHttpException(\sprintf('Either catalog "%s" does not exist or you can\'t access it, or product "%s" does not exist or you do not have permission to access it.',
                $id, $productUuid), $e);
        }

        if (null === $catalog) {
            throw new NotFoundHttpException(\sprintf('Either catalog "%s" does not exist or you can\'t access it, or product "%s" does not exist or you do not have permission to access it.',
                $id, $productUuid));
        }

        return $catalog;
    }
}
