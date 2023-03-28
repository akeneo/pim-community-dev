<?php

declare(strict_types=1);

namespace Akeneo\Catalogs\Domain;

use Akeneo\Catalogs\Infrastructure\Validation\CatalogProductMapping;
use Akeneo\Catalogs\Infrastructure\Validation\CatalogProductSelectionCriteria;
use Akeneo\Catalogs\Infrastructure\Validation\CatalogProductValueFilters;
use Akeneo\Catalogs\Infrastructure\Validation\ProductMapping\ProductMappingRespectsSchema;

/**
 * @copyright 2022 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 *
 * @phpstan-type ProductSelectionCriteria array<array{
 *      field: string,
 *      operator: string,
 *      value?: mixed,
 *      scope?: string,
 *      locale?: string,
 * }>
 *
 * @phpstan-type ProductValueFilters array{
 *      channels?: array<string>,
 *      locales?: array<string>,
 *      currencies?: array<string>
 * }
 *
 * @phpstan-type SourceAssociation array{
 *      source: string|null,
 *      locale: string|null,
 *      scope: string|null,
 *      default?: string|boolean|numeric|null,
 *      parameters?: array{
 *          label_locale?: string|null,
 *          currency?: string|null,
 *          unit?: string|null,
 *          sub_source?: string|null,
 *          sub_scope?: string|null,
 *          sub_locale?: string|null
 *      }
 * }
 *
 * @phpstan-type ProductMapping array<string, SourceAssociation>
 */
#[ProductMappingRespectsSchema]
final class Catalog
{
    /**
     * @param ProductSelectionCriteria $productSelectionCriteria
     * @param ProductValueFilters $productValueFilters
     * @param ProductMapping $productMapping
     */
    public function __construct(
        private string $id,
        private string $name,
        private string $ownerUsername,
        private bool $enabled,
        #[CatalogProductSelectionCriteria]
        private array $productSelectionCriteria,
        #[CatalogProductValueFilters]
        private array $productValueFilters,
        #[CatalogProductMapping]
        private array $productMapping,
    ) {
    }

    public function getId(): string
    {
        return $this->id;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function getOwnerUsername(): string
    {
        return $this->ownerUsername;
    }

    public function isEnabled(): bool
    {
        return $this->enabled;
    }

    /**
     * @return ProductSelectionCriteria
     */
    public function getProductSelectionCriteria(): array
    {
        return $this->productSelectionCriteria;
    }

    /**
     * @return ProductValueFilters
     */
    public function getProductValueFilters(): array
    {
        return $this->productValueFilters;
    }

    /**
     * @return ProductMapping
     */
    public function getProductMapping(): array
    {
        return $this->productMapping;
    }

    /**
     * @return array{
     *     id: string,
     *     name: string,
     *     enabled: bool,
     *     owner_username: string,
     *     product_selection_criteria: ProductSelectionCriteria,
     *     product_value_filters: ProductValueFilters,
     *     product_mapping: ProductMapping
     * }
     */
    public function normalize(): array
    {
        return [
            'id' => $this->getId(),
            'name' => $this->getName(),
            'enabled' => $this->isEnabled(),
            'owner_username' => $this->getOwnerUsername(),
            'product_selection_criteria' => $this->getProductSelectionCriteria(),
            'product_value_filters' => $this->getProductValueFilters(),
            'product_mapping' => $this->getProductMapping(),
        ];
    }
}
