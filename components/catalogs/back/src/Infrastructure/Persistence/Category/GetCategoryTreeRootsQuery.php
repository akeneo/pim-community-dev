<?php

declare(strict_types=1);

namespace Akeneo\Catalogs\Infrastructure\Persistence\Category;

use Akeneo\Catalogs\Application\Persistence\Category\GetCategoryTreeRootsQueryInterface;
use Akeneo\Category\Api\CategoryTree;
use Akeneo\Category\Api\FindCategoryTrees;

/**
 * @copyright 2022 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class GetCategoryTreeRootsQuery implements GetCategoryTreeRootsQueryInterface
{
    public function __construct(
        private FindCategoryTrees $findCategoryTrees,
    ) {
    }

    /**
     * @inheritDoc
     */
    public function execute(string $locale = 'en_US'): array
    {
        $categoryTrees = $this->findCategoryTrees->execute();

        $normalizedCategoryTrees = [];
        foreach ($categoryTrees as $categoryTree) {
            $normalizedCategoryTrees[] = $this->normalizeCategoryTree($categoryTree, $locale);
        }

        return $normalizedCategoryTrees;
    }

    /**
     * @return array{code: string, label: string, isLeaf: bool}
     */
    private function normalizeCategoryTree(CategoryTree $categoryTree, string $locale): array
    {
        return [
            'code' => $categoryTree->code,
            'label' => $categoryTree->labels[$locale] ?? "[{$categoryTree->code}]",
            'isLeaf' => false,
        ];
    }
}
