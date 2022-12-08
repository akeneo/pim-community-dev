<?php

declare(strict_types=1);

namespace Akeneo\Category\Infrastructure\Handler;

use Akeneo\Category\Application\Handler\GetPositionInterface;
use Akeneo\Category\Application\Query\GetDirectChildrenCategoryCodesInterface;
use Akeneo\Category\Domain\Model\Enrichment\Category;

/**
 * @copyright 2022 Akeneo SAS (https://www.akeneo.com)
 * @license https://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 */
class GetPositionHandler implements GetPositionInterface
{
    public function __construct(private readonly GetDirectChildrenCategoryCodesInterface $getDirectChildrenCategoryCodes)
    {
    }

    public function __invoke(Category $category): int
    {
        if ($category->isRoot()) {
            return 1;
        }

        $children = $this->getDirectChildrenCategoryCodes->execute($category->getParentId()->getValue());

        return $this->getCategoryPositionAmongChildren($category, $children);
    }

    /**
     * @param array<string, array{code: string, row_num: int}> $children
     */
    private function getCategoryPositionAmongChildren(Category $category, array $children): int
    {
        return (int) ($children[(string) $category->getCode()]['row_num'] ?? 1);
    }
}
