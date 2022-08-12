<?php

declare(strict_types=1);

namespace Akeneo\Catalogs\Infrastructure\Persistence;

use Akeneo\Catalogs\Application\Persistence\GetCategoryTreeRootsQueryInterface;
use Akeneo\Category\Api\CategoryTree;
use Akeneo\Category\Api\FindCategoryTrees;
use Akeneo\UserManagement\Bundle\Context\UserContext;

/**
 * @copyright 2022 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class GetCategoryTreeRootsQuery implements GetCategoryTreeRootsQueryInterface
{
    public function __construct(
        private UserContext $userContext,
        private FindCategoryTrees $findCategoryTrees,
    ) {
    }

    /**
     * @inheritDoc
     */
    public function execute(): array
    {
        $locale = $this->userContext->getCurrentLocale()->getCode();
        $categoryTrees = $this->findCategoryTrees->execute();

        $normalizedCategoryTrees = [];
        foreach ($categoryTrees as $categoryTree) {
            $normalizedCategoryTrees[] = $this->normalizeCategoryTree($categoryTree, $locale);
        }

        return $normalizedCategoryTrees;
    }

    /**
     * @return array{id: int, code: string, label: string, isLeaf: bool}
     */
    private function normalizeCategoryTree(CategoryTree $categoryTree, string $locale): array
    {
        /** @var array{id: int, code: string, labels: array<string, string>} $normalizedTree */
        $normalizedTree = $categoryTree->normalize();

        $normalizedTree['isLeaf'] = false;
        $normalizedTree['label'] = $normalizedTree['labels'][$locale] ?? "[{$normalizedTree['code']}]";
        unset($normalizedTree['labels']);

        return $normalizedTree;
    }
}
