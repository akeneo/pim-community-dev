<?php

declare(strict_types=1);

namespace Akeneo\Catalogs\Application\Handler;

use Akeneo\Catalogs\Application\Exception\CatalogNotFoundException;
use Akeneo\Catalogs\Application\Exception\ProductMappingSchemaNotFoundException;
use Akeneo\Catalogs\Application\Persistence\Catalog\GetCatalogQueryInterface;
use Akeneo\Catalogs\Application\Persistence\ProductMappingSchema\GetProductMappingSchemaQueryInterface;
use Akeneo\Catalogs\ServiceAPI\Exception\CatalogNotFoundException as ServiceApiCatalogNotFoundException;
use Akeneo\Catalogs\ServiceAPI\Exception\ProductMappingSchemaNotFoundException as ServiceApiProductMappingSchemaNotFoundException;
use Akeneo\Catalogs\ServiceAPI\Query\GetProductMappingSchemaQuery;

/**
 * @copyright 2022 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
final class GetProductMappingSchemaHandler
{
    public function __construct(
        private GetCatalogQueryInterface $getCatalogQuery,
        private GetProductMappingSchemaQueryInterface $getProductMappingSchemaQuery,
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

        try {
            $productMappingSchema = $this->getProductMappingSchemaQuery->execute($catalog->getId());
        } catch (ProductMappingSchemaNotFoundException) {
            throw new ServiceApiProductMappingSchemaNotFoundException();
        }

        return (object) $productMappingSchema;
    }
}
