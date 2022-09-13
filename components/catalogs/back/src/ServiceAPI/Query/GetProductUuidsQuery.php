<?php

declare(strict_types=1);

namespace Akeneo\Catalogs\ServiceAPI\Query;

use Symfony\Component\Validator\Constraints as Assert;

/**
 * @copyright 2022 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 *
 * @implements QueryInterface<array<string>>
 */
class GetProductUuidsQuery implements QueryInterface
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
