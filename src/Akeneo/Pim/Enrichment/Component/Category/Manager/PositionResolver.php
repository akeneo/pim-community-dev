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
        if (!$this->hasParent($category)) {
            return 1;
        }

        $children = $this->getDirectChildrenCategoryCodes->execute($category->getParent());

        if ($this->isLeafCategory($children)) {
            return 1;
        }

        return $this->getCategoryPositionInChildren($category, $children);
    }

    /**
     * @param array<string> $children
     */
    private function isLeafCategory(array $children): bool
    {
        return 0 === count($children);
    }

    private function hasParent(CategoryInterface $category): bool
    {
        return null !== $category->getParent();
    }

    /**
     * @param array<string> $children
     */
    private function getCategoryPositionInChildren(CategoryInterface $category, array $children): int
    {
        $search = array_search($category->getCode(), $children);

        return $search ? $search + 1 : 1;
    }
}
