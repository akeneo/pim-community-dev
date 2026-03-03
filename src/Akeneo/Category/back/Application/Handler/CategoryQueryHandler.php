<?php

declare(strict_types=1);

namespace Akeneo\Category\Application\Handler;

use Akeneo\Category\Domain\Query\GetCategoryInterface;
use Akeneo\Category\ServiceApi\Category;
use Akeneo\Category\ServiceApi\CategoryQueryInterface;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

/**
 * @copyright 2022 Akeneo SAS (https://www.akeneo.com)
 * @license   https://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class CategoryQueryHandler implements CategoryQueryInterface
{
    public function __construct(
        private GetCategoryInterface $getCategory,
    ) {
    }

    public function byId(int $categoryId): Category
    {
        $category = $this->getCategory->byId($categoryId);

        if ($category === null) {
            throw new NotFoundHttpException();
        }

        $categoryNormalized = $category->normalize();

        return new Category(
            $categoryNormalized['id'],
            $categoryNormalized['properties']['code'],
            $categoryNormalized['properties']['labels'],
            $categoryNormalized['parent'],
            $categoryNormalized['attributes'],
            $categoryNormalized['permissions'],
        );
    }

    public function byCode(string $categoryCode): Category
    {
        $category = $this->getCategory->byCode($categoryCode);

        if ($category === null) {
            throw new NotFoundHttpException();
        }

        $categoryNormalized = $category->normalize();

        return new Category(
            $categoryNormalized['id'],
            $categoryNormalized['properties']['code'],
            $categoryNormalized['properties']['labels'],
            $categoryNormalized['parent'],
            $categoryNormalized['attributes'],
            $categoryNormalized['permissions'],
        );
    }

    /**
     * @param array<string> $categoryCodes
     *
     * @return \Generator<Category>
     */
    public function byCodes(array $categoryCodes): \Generator
    {
        $categories = $this->getCategory->byCodes(array_values($categoryCodes));
        foreach ($categories as $category) {
            $categoryNormalized = $category->normalize();

            yield new Category(
                $categoryNormalized['id'],
                $categoryNormalized['properties']['code'],
                $categoryNormalized['properties']['labels'],
                $categoryNormalized['parent'],
                $categoryNormalized['attributes'],
                $categoryNormalized['permissions'],
            );
        }
    }

    /**
     * @param array<int> $categoryIds
     *
     * @return \Generator<Category>
     */
    public function byIds(array $categoryIds): \Generator
    {
        $categories = $this->getCategory->byIds(array_values($categoryIds));
        foreach ($categories as $category) {
            $categoryNormalized = $category->normalize();

            yield new Category(
                $categoryNormalized['id'],
                $categoryNormalized['properties']['code'],
                $categoryNormalized['properties']['labels'],
                $categoryNormalized['parent'],
                $categoryNormalized['attributes'],
                $categoryNormalized['permissions'],
            );
        }
    }
}
