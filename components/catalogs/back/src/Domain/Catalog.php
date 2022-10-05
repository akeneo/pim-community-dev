<?php

declare(strict_types=1);

namespace Akeneo\Catalogs\Domain;

/**
 * @copyright 2022 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 *
 * @phpstan-type ProductSelectionCriterion array{
 *      field: string,
 *      operator: string,
 *      value?: mixed,
 *      scope?: string,
 *      locale?: string,
 * }
 *
 * @phpstan-type ProductValueFilters array{
 *      channels?: array<string>,
 *      locales?: array<string>,
 *      currencies?: array<string>,
 * }
 */
final class Catalog
{
    /**
     * @param array<array-key, ProductSelectionCriterion> $productSelectionCriteria
     * @param ProductValueFilters $productValueFilters
     */
    public function __construct(
        private string $id,
        private string $name,
        private string $ownerUsername,
        private bool $enabled,
        private array $productSelectionCriteria,
        private array $productValueFilters,
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
     * @return array<array-key, ProductSelectionCriterion>
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
}
