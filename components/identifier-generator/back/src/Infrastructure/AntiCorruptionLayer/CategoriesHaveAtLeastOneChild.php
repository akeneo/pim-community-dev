<?php

declare(strict_types=1);

namespace Akeneo\Pim\Automation\IdentifierGenerator\Infrastructure\AntiCorruptionLayer;

use Akeneo\Pim\Automation\IdentifierGenerator\Domain\Query\CategoriesHaveAtLeastOneChild as BaseCategoriesHaveAtLeastOneChild;
use Akeneo\Pim\Enrichment\Category\API\Query\CategoriesHaveAtLeastOneChild as OriginalCategoriesHaveAtLeastOneChild;

class CategoriesHaveAtLeastOneChild implements BaseCategoriesHaveAtLeastOneChild
{
    public function __construct(
        private readonly OriginalCategoriesHaveAtLeastOneChild $categoriesHaveAtLeastOneChild,
    ) {
    }

    public function among(array $parentCategoryCodes, array $childrenCategoryCodes): bool
    {
        return $this->categoriesHaveAtLeastOneChild->among($parentCategoryCodes, $childrenCategoryCodes);
    }
}
