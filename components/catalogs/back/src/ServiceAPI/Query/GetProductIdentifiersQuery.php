<?php

declare(strict_types=1);

namespace Akeneo\Catalogs\ServiceAPI\Query;

use Akeneo\Catalogs\Domain\Catalog;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @copyright 2022 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 *
 * @implements QueryInterface<array<string>>
 * @codeCoverageIgnore
 */
class GetProductIdentifiersQuery implements QueryInterface
{
    public function __construct(
        #[Assert\NotBlank]
        #[Assert\Uuid]
        private Catalog $catalog,
        #[Assert\Uuid]
        private ?string $searchAfter = null,
        #[Assert\Range(min: 1, max: 1000)]
        private int $limit = 100,
    ) {
    }

    public function getCatalog(): Catalog
    {
        return $this->catalog;
    }

    public function getSearchAfter(): ?string
    {
        return $this->searchAfter;
    }

    public function getLimit(): int
    {
        return $this->limit;
    }
}
