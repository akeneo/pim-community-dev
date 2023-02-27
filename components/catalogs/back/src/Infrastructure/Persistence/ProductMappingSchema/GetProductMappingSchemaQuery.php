<?php

declare(strict_types=1);

namespace Akeneo\Catalogs\Infrastructure\Persistence\ProductMappingSchema;

use Akeneo\Catalogs\Application\Exception\ProductMappingSchemaNotFoundException;
use Akeneo\Catalogs\Application\Persistence\ProductMappingSchema\GetProductMappingSchemaQueryInterface;
use Akeneo\Catalogs\Application\Storage\CatalogsMappingStorageInterface;

/**
 * @copyright 2022 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
final class GetProductMappingSchemaQuery implements GetProductMappingSchemaQueryInterface
{
    public function __construct(
        private readonly CatalogsMappingStorageInterface $catalogsMappingStorage,
    ) {
    }

    public function execute(string $catalogId): array
    {
        $productMappingSchemaFile = \sprintf('%s_product.json', $catalogId);

        if (!$this->catalogsMappingStorage->exists($productMappingSchemaFile)) {
            throw new ProductMappingSchemaNotFoundException();
        }

        $productMappingSchemaRaw = \stream_get_contents(
            $this->catalogsMappingStorage->read($productMappingSchemaFile),
        );

        if (false === $productMappingSchemaRaw) {
            throw new \LogicException('Product mapping schema is unreadable.');
        }

        /**
         * @var array{
         *      properties: array<array-key, mixed>
         * } $productMappingSchema
         */
        $productMappingSchema = \json_decode($productMappingSchemaRaw, true, 512, JSON_THROW_ON_ERROR);

        return $productMappingSchema;
    }
}
