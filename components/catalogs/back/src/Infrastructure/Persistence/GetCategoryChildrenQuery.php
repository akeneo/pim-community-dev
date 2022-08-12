<?php

declare(strict_types=1);

namespace Akeneo\Catalogs\Infrastructure\Persistence;

use Akeneo\Catalogs\Application\Persistence\GetCategoryChildrenQueryInterface;
use Akeneo\Category\Infrastructure\Component\Classification\Repository\CategoryRepositoryInterface;
use Akeneo\Category\Infrastructure\Component\Model\CategoryInterface;

/**
 * @copyright 2022 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class GetCategoryChildrenQuery implements GetCategoryChildrenQueryInterface
{
    public function __construct(private CategoryRepositoryInterface $categoryRepository)
    {
    }

    /**
     * @inheritDoc
     */
    public function execute(int $categoryId): array
    {
        $categories = $this->categoryRepository->getChildrenByParentId($categoryId);

        $children = [];
        /** @var CategoryInterface $category */
        foreach ($categories as $category) {
            $children[] = $this->normalizeCategory($category);
        }

        return $children;
    }

    /**
     * @return array{id: int, code: string, label: string, isLeaf: bool}
     */
    private function normalizeCategory(CategoryInterface $category): array
    {
        return [
            'id' => $category->getId(),
            'code' => $category->getCode(),
            'label' => $category->getLabel(),
            'isLeaf' => $category->getRight() - $category->getLeft() === 1,
        ];
    }
}
