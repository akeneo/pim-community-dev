<?php

declare(strict_types=1);

namespace Akeneo\Catalogs\Domain;

/**
 * @copyright 2022 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 *
 * @phpstan-type ProductValueFilters array{
 *      channels?: array<string>,
 *      locales?: array<string>,
 *      currencies?: array<string>,
 * }
 *
 * @phpstan-type ProductMapping array<string, array{
 *          source: string|null,
 *          locale: string|null,
 *          scope: string|null,
 * }>
 *
 * @phpstan-import-type ProductSelectionCriterion from ProductSelectionCriteria
 */
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
        private ProductSelectionCriteria $productSelectionCriteria,
        private array $productValueFilters,
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

    public function getProductSelectionCriteria(): ProductSelectionCriteria
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
     *     product_selection_criteria: array<ProductSelectionCriterion>,
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
            'product_selection_criteria' => $this->getProductSelectionCriteria()->toArray(),
            'product_value_filters' => $this->getProductValueFilters(),
            'product_mapping' => $this->getProductMapping(),
        ];
    }
}
