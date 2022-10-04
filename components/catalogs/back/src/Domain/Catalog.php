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
 */
final class Catalog
{
    /**
     * @param string $id
     * @param string $name
     * @param string $ownerUsername
     * @param bool   $enabled
     * @param array<array-key, ProductSelectionCriterion> $productSelectionCriteria
     * @param array{channels?: array<string>, locales?: array<string>, currencies?: array<string>} $productValueFilters
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
     * @return array{channels?: array<string>, locales?: array<string>, currencies?: array<string>}
     */
    public function getProductValueFilters(): array
    {
        return $this->productValueFilters;
    }
}
