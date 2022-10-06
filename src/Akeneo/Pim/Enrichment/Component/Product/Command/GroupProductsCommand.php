<?php

declare(strict_types=1);

namespace Akeneo\Pim\Enrichment\Component\Product\Command;

/**
 * @copyright 2021 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class GroupProductsCommand
{
    public function __construct(
        private int $groupId,
        private array $productUuids,
    ) {
    }

    public function groupId(): int
    {
        return $this->groupId;
    }

    /**
     * @return string[]
     */
    public function productUuids(): array
    {
        return $this->productUuids;
    }
}
