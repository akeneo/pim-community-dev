<?php

declare(strict_types=1);

namespace Akeneo\Catalogs\Infrastructure\Persistence\Category;

use Akeneo\Catalogs\Application\Persistence\Category\GetCategoryChildrenQueryInterface;
use Akeneo\Category\Infrastructure\Component\Classification\Repository\CategoryRepositoryInterface;
use Akeneo\Category\Infrastructure\Component\Model\CategoryInterface;
use Akeneo\Category\Infrastructure\Component\Model\CategoryTranslationInterface;
use Doctrine\Common\Collections\Collection;

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
    public function execute(string $categoryCode, string $locale = 'en_US'): array
    {
        $parentCategory = $this->getCategoryFromCode($categoryCode);
        if ($parentCategory === null) {
            return [];
        }

        $categories = $this->categoryRepository->getChildrenByParentId($parentCategory->getId());

        $children = [];
        /** @var CategoryInterface $category */
        foreach ($categories as $category) {
            $children[] = $this->normalizeCategory($category, $locale);
        }

        return $children;
    }

    private function getCategoryFromCode(string $categoryCode): ?CategoryInterface
    {
        /** @var Collection<int, CategoryInterface> $categories */
        $categories = $this->categoryRepository->getCategoriesByCodes([$categoryCode]);

        return $categories->count() > 0 ? $categories[0] : null;
    }

    /**
     * @return array{code: string, label: string, isLeaf: bool}
     */
    private function normalizeCategory(CategoryInterface $category, string $locale): array
    {
        /** @var CategoryTranslationInterface|null $categoryTranslation */
        $categoryTranslation = $category->getTranslation($locale);
        $label = $categoryTranslation?->getLabel() ?? "[{$category->getCode()}]";

        return [
            'code' => $category->getCode(),
            'label' => $label,
            'isLeaf' => $category->getRight() - $category->getLeft() === 1,
        ];
    }
}
