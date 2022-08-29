<?php

declare(strict_types=1);

namespace Akeneo\Category\Application\Handler;

use Akeneo\Category\Domain\Model\Category as DomainCategory;
use Akeneo\Category\Domain\Query\GetCategoryInterface;
use Akeneo\Category\ServiceApi\Category;
use Akeneo\Category\ServiceApi\CategoryInterface;

/**
 * @copyright 2022 Akeneo SAS (https://www.akeneo.com)
 * @license   https://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class CategoryHandler implements CategoryInterface
{
    public function __construct(
        private GetCategoryInterface $getCategory,
    ) {
    }

    public function byCode(string $categoryCode): ?Category
    {
        /** @var DomainCategory $result */
        $result = $this->getCategory->byCode($categoryCode);

        if (!$result) {
            return null;
        }

        $categoryNormalized = $result->normalize();

        return new Category(
            $categoryNormalized['id'],
            $categoryNormalized['code'],
            $categoryNormalized['labels'],
            $categoryNormalized['parent'],
            $categoryNormalized['values'],
            $categoryNormalized['permissions'],
        );
    }
}
