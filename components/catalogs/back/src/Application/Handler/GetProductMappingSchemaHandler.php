<?php

declare(strict_types=1);

namespace Akeneo\Catalogs\Application\Handler;

use Akeneo\Catalogs\Application\Exception\CatalogNotFoundException;
use Akeneo\Catalogs\Application\Persistence\Catalog\GetCatalogQueryInterface;
use Akeneo\Catalogs\Application\Storage\CatalogsMappingStorageInterface;
use Akeneo\Catalogs\ServiceAPI\Exception\CatalogNotFoundException as ServiceApiCatalogNotFoundException;
use Akeneo\Catalogs\ServiceAPI\Exception\ProductSchemaMappingNotFoundException as ServiceApiProductSchemaMappingNotFoundException;
use Akeneo\Catalogs\ServiceAPI\Query\GetProductMappingSchemaQuery;

/**
 * @copyright 2022 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
final class GetProductMappingSchemaHandler
{
    public function __construct(
        private GetCatalogQueryInterface $getCatalogQuery,
        private CatalogsMappingStorageInterface $catalogsMappingStorage,
    ) {
    }

    /**
     * @psalm-suppress MixedReturnStatement
     * @psalm-suppress MixedInferredReturnType
     */
    public function __invoke(GetProductMappingSchemaQuery $query): object
    {
        try {
            $catalog = $this->getCatalogQuery->execute($query->getCatalogId());
        } catch (CatalogNotFoundException) {
            throw new ServiceApiCatalogNotFoundException();
        }

        $productMappingSchemaFile = \sprintf('%s_product.json', $catalog->getId());

        if (!$this->catalogsMappingStorage->exists($productMappingSchemaFile)) {
            throw new ServiceApiProductSchemaMappingNotFoundException();
        }

        $productMappingSchema = \stream_get_contents(
            $this->catalogsMappingStorage->read($productMappingSchemaFile),
        );

        if (false === $productMappingSchema) {
            throw new \LogicException('Product mapping schema is unreadable.');
        }

        return \json_decode($productMappingSchema, false, 512, JSON_THROW_ON_ERROR);
    }
}
