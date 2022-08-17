<?php

declare(strict_types=1);

namespace Akeneo\Catalogs\Infrastructure\Persistence;

use Akeneo\Catalogs\Application\Persistence\GetCategoriesByCodeQueryInterface;
use Akeneo\Category\Infrastructure\Component\Classification\Repository\CategoryRepositoryInterface;
use Akeneo\Category\Infrastructure\Component\Model\CategoryInterface;

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
    public function execute(array $categoryCodes): array
    {
        $categories = $this->categoryRepository->getCategoriesByCodes($categoryCodes);

        $normalizedCategories = [];
        /** @var CategoryInterface $category */
        foreach ($categories as $category) {
            $normalizedCategories[] = $this->normalizeCategory($category);
        }

        return $normalizedCategories;
    }

    /**
     * @return array{code: string, label: string, isLeaf: bool}
     */
    private function normalizeCategory(CategoryInterface $category): array
    {
        return [
            'code' => $category->getCode(),
            'label' => $category->getLabel(),
            'isLeaf' => $category->getRight() - $category->getLeft() === 1,
        ];
    }
}
