<?php

declare(strict_types=1);

namespace Akeneo\Catalogs\ServiceAPI\Query;

use Symfony\Component\Validator\Constraints as Assert;

/**
 * @copyright 2022 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 *
 * @phpstan-type ProductValue array{
 *      scope: string|null,
 *      locale: string|null,
 *      data: mixed,
 * }
 *
 * @phpstan-type Product array{
 *      uuid: string,
 *      enabled: boolean,
 *      family: string,
 *      categories: array<string>,
 *      groups: array<string>,
 *      parent: string|null,
 *      values: array<string, ProductValue>,
 *      associations: array<string, array{groups: array<string>, products: array<string>, product_models: array<string>}>,
 *      quantified_associations: array<string, array{products: array<string>, product_models: array<string>}>,
 *      created: string,
 *      updated: string,
 * }
 *
 * @implements QueryInterface<array<Product>>
 */
class GetProductsQuery implements QueryInterface
{
    public function __construct(
        #[Assert\NotBlank]
        #[Assert\Uuid]
        private string $catalogId,
        #[Assert\Uuid]
        private ?string $searchAfter = null,
        #[Assert\Range(min: 1, max: 1000)]
        private int $limit = 100,
        #[Assert\DateTime(\DateTimeInterface::ATOM, 'ISO 8601 format is required.')]
        private ?string $updatedAfter = null,
        #[Assert\DateTime(\DateTimeInterface::ATOM, 'ISO 8601 format is required.')]
        private ?string $updatedBefore = null,
    ) {
    }

    public function getCatalogId(): string
    {
        return $this->catalogId;
    }

    public function getSearchAfter(): ?string
    {
        return $this->searchAfter;
    }

    public function getLimit(): int
    {
        return $this->limit;
    }

    public function getUpdatedAfter(): ?string
    {
        return $this->updatedAfter;
    }

    public function getUpdatedBefore(): ?string
    {
        return $this->updatedBefore;
    }
}
