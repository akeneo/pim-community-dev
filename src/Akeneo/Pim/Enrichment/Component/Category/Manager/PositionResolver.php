<?php
declare(strict_types=1);

namespace Akeneo\Pim\Enrichment\Component\Category\Manager;

use Akeneo\Pim\Enrichment\Component\Category\Query\GetDirectChildrenCategoryCodesInterface;
use Akeneo\Tool\Component\Classification\Model\CategoryInterface;

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
     * @param array<string> $children
     */
    private function getCategoryPositionAmongChildren(CategoryInterface $category, array $children): int
    {
        $search = array_search($category->getCode(), $children);

        return $search ? $search + 1 : 1;
    }
}
