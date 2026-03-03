<?php

declare(strict_types=1);

namespace Akeneo\Pim\Automation\IdentifierGenerator\Infrastructure\AntiCorruptionLayer;

use Akeneo\Category\ServiceApi\Query\CategoriesHaveAtLeastOneChild as OriginalCategoriesHaveAtLeastOneChild;
use Akeneo\Pim\Automation\IdentifierGenerator\Domain\Query\CategoriesHaveAtLeastOneChild as BaseCategoriesHaveAtLeastOneChild;

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
