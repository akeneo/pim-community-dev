<?php

declare(strict_types=1);

namespace Akeneo\Category\Infrastructure\Storage\InMemory;

use Akeneo\Category\Domain\Model\Enrichment\Category;
use Akeneo\Category\Domain\Query\GetCategoryInterface;
use Akeneo\Category\Domain\ValueObject\CategoryId;
use Akeneo\Category\Domain\ValueObject\Code;
use Akeneo\Category\Domain\ValueObject\LabelCollection;
use Akeneo\Category\Infrastructure\Component\Classification\Repository\CategoryRepositoryInterface;

/**
 * @copyright 2023 Akeneo SAS (https://www.akeneo.com)
 * @license   https://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
final class GetCategoryInMemory implements GetCategoryInterface
{
    public function __construct(
        private readonly CategoryRepositoryInterface $categoryRepository,
    ) {
    }

    public function byId(int $categoryId): ?Category
    {
        throw new \Exception(sprintf('The method %s is not implemented yet', __METHOD__));
    }

    public function byCode(string $categoryCode): ?Category
    {
        throw new \Exception(sprintf('The method %s is not implemented yet', __METHOD__));
    }

    public function byCodes(array $categoryCodes): \Generator
    {
        $categories = $this->categoryRepository->getCategoriesByCodes($categoryCodes);
        foreach ($categories as $index => $category) {
            yield new Category(
                id: new CategoryId($category->getId() ?: ($index + 1)),
                code: new Code($category->getCode()),
                templateUuid: null,
                labels: LabelCollection::fromArray([]),
            );
        }
    }

    public function byIds(array $categoryIds): \Generator
    {
        throw new \Exception(sprintf('The method %s is not implemented yet', __METHOD__));
    }
}
