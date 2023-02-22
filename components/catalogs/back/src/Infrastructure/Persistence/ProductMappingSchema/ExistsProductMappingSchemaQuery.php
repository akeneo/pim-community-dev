<?php

declare(strict_types=1);

namespace Akeneo\Catalogs\Infrastructure\Persistence\ProductMappingSchema;

use Akeneo\Catalogs\Application\Persistence\ProductMappingSchema\ExistsProductMappingSchemaQueryInterface;
use Akeneo\Catalogs\Application\Storage\CatalogsMappingStorageInterface;

/**
 * @copyright 2023 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
final class ExistsProductMappingSchemaQuery implements ExistsProductMappingSchemaQueryInterface
{
    public function __construct(
        private readonly CatalogsMappingStorageInterface $catalogsMappingStorage,
    ) {
    }

    public function execute(string $catalogId): bool
    {
        return $this->catalogsMappingStorage->exists(
            \sprintf('%s_product.json', $catalogId),
        );
    }
}
