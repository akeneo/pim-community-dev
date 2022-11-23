<?php

declare(strict_types=1);

namespace Akeneo\Category\Infrastructure\Component\Manager;

use Akeneo\Category\Infrastructure\Component\Classification\Model\CategoryInterface;
use Akeneo\Pim\Enrichment\Component\Category\Query\GetDirectChildrenCategoryCodesInterface;

/**
 * @copyright 2022 Akeneo SAS (https://www.akeneo.com)
 * @license https://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 */
class PositionResolver implements PositionResolverInterface
{
    public function __construct(private GetDirectChildrenCategoryCodesInterface $getDirectChildrenCategoryCodes)
    {
    }

    public function getPosition(CategoryInterface $category): int
    {
        if ($category->isRoot()) {
            return 1;
        }

        $children = $this->getDirectChildrenCategoryCodes->execute($category->getParent()->getId());

        return $this->getCategoryPositionAmongChildren($category, $children);
    }

    /**
     * @param array<string, array{code: string, row_num: int}> $children
     */
    private function getCategoryPositionAmongChildren(CategoryInterface $category, array $children): int
    {
        return (int) ($children[$category->getCode()]['row_num'] ?? 1);
    }
}
