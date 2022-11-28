<?php

declare(strict_types=1);

namespace Akeneo\Category\Infrastructure\Component\Manager;

use Akeneo\Category\Domain\Model\Enrichment\Category;
use Akeneo\Category\Infrastructure\Component\Classification\Model\CategoryInterface;
use Akeneo\Pim\Enrichment\Component\Category\Query\GetDirectChildrenCategoryCodesInterface;
use Akeneo\Platform\Bundle\FeatureFlagBundle\FeatureFlags;

/**
 * @copyright 2022 Akeneo SAS (https://www.akeneo.com)
 * @license https://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 */
class PositionResolver implements PositionResolverInterface
{
    public function __construct(
        private readonly GetDirectChildrenCategoryCodesInterface $getDirectChildrenCategoryCodes,
        private readonly FeatureFlags $featureFlags,
    ) {
    }

    public function getPosition(CategoryInterface|Category $category): int
    {
        if ($category->isRoot()) {
            return 1;
        }

        $parentId = $this->featureFlags->isEnabled('enriched_category') && $category instanceof Category ?
            $category->getParentId()->getValue() : $category->getParent()->getId();

        $children = $this->getDirectChildrenCategoryCodes->execute($parentId);

        return $this->getCategoryPositionAmongChildren($category, $children);
    }

    /**
     * @param array<string, array{code: string, row_num: int}> $children
     */
    private function getCategoryPositionAmongChildren(CategoryInterface|Category $category, array $children): int
    {
        return (int) ($children[(string) $category->getCode()]['row_num'] ?? 1);
    }
}
