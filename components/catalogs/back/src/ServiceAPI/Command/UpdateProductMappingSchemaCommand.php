<?php

declare(strict_types=1);

namespace Akeneo\Catalogs\ServiceAPI\Command;

use Akeneo\Catalogs\Infrastructure\Validation as CatalogAssert;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @copyright 2022 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 *
 * @codeCoverageIgnore
 */
final class UpdateProductMappingSchemaCommand implements CommandInterface
{
    /**
     * @param array{properties: array<array-key, mixed>} $productMappingSchema
     */
    public function __construct(
        #[Assert\NotBlank]
        #[Assert\Uuid]
        private string $catalogId,
        #[Assert\NotBlank]
        #[CatalogAssert\ProductSchema]
        private array $productMappingSchema,
    ) {
    }

    public function getCatalogId(): string
    {
        return $this->catalogId;
    }

    /**
     * @return array{properties: array<array-key, mixed>}
     */
    public function getProductMappingSchema(): array
    {
        return $this->productMappingSchema;
    }
}
