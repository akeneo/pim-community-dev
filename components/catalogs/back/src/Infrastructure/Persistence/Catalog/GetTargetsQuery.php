<?php

declare(strict_types=1);

namespace Akeneo\Catalogs\Infrastructure\Persistence\Catalog;

use Akeneo\Catalogs\Application\Persistence\Catalog\GetTargetsQueryInterface;
use Akeneo\Catalogs\Application\Storage\CatalogsMappingStorageInterface;

/**
 * @copyright 2022 Akeneo SAS (https://www.akeneo.com)
 * @license   https://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
final class GetTargetsQuery implements GetTargetsQueryInterface
{
    public function __construct(
        private CatalogsMappingStorageInterface $catalogsMappingStorage,
    ) {
    }

    public function execute(string $catalogId): ?array
    {
        $productMappingSchemaFile = \sprintf('%s_product.json', $catalogId);

        if (!$this->catalogsMappingStorage->exists($productMappingSchemaFile)) {
            return null;
        }

        $productMappingSchemaRaw = \stream_get_contents(
            $this->catalogsMappingStorage->read($productMappingSchemaFile)
        );

        if (false === $productMappingSchemaRaw) {
            throw new \LogicException('Product mapping schema is unreadable.');
        }

        $productMappingSchema = \json_decode($productMappingSchemaRaw, true, 512, JSON_THROW_ON_ERROR);

        $targets = [];

        foreach ($productMappingSchema['properties'] as $targetCode => $property) {
            $targets[] = [
                'code' => $targetCode,
                'label' => \array_key_exists('title', $property) ? $property['title'] : $targetCode,
            ];
        }

        return $targets;
    }
}
