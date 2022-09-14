<?php

declare(strict_types=1);

namespace Akeneo\Catalogs\Infrastructure\Persistence\Category;

use Akeneo\Catalogs\Application\Persistence\Category\GetCategoriesByCodeQueryInterface;
use Akeneo\Category\Infrastructure\Component\Classification\Repository\CategoryRepositoryInterface;
use Akeneo\Category\Infrastructure\Component\Model\CategoryInterface;
use Akeneo\Category\Infrastructure\Component\Model\CategoryTranslationInterface;

/**
 * @copyright 2022 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class GetCategoriesByCodeQuery implements GetCategoriesByCodeQueryInterface
{
    public function __construct(private CategoryRepositoryInterface $categoryRepository)
    {
    }

    /**
     * @inheritDoc
     */
    public function execute(array $categoryCodes, string $locale = 'en_US'): array
    {
        $categories = $this->categoryRepository->getCategoriesByCodes($categoryCodes);

        $normalizedCategories = [];
        /** @var CategoryInterface $category */
        foreach ($categories as $category) {
            $normalizedCategories[] = $this->normalizeCategory($category, $locale);
        }

        return $normalizedCategories;
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
