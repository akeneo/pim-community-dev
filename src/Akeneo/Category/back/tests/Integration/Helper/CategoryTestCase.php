<?php

declare(strict_types=1);

/**
 * @copyright 2022 Akeneo SAS (https://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

namespace Akeneo\Category\back\tests\Integration\Helper;

use Akeneo\Category\Application\Storage\Save\Query\UpsertCategoryBase;
use Akeneo\Category\Application\Storage\Save\Query\UpsertCategoryTranslations;
use Akeneo\Category\Domain\Model\Category;
use Akeneo\Category\Domain\ValueObject\CategoryId;
use Akeneo\Category\Domain\ValueObject\Code;
use Akeneo\Category\Domain\ValueObject\LabelCollection;
use Akeneo\Category\Infrastructure\Storage\Save\Query\UpsertCategoryBaseSql;
use Akeneo\Category\Infrastructure\Storage\Save\Query\UpsertCategoryTranslationsSql;
use Akeneo\Category\Infrastructure\Storage\Sql\GetCategorySql;

trait CategoryTestCase
{
    /**
     * @param array<string, string>|null $labels
     *
     * @throws \Doctrine\DBAL\Driver\Exception
     * @throws \Doctrine\DBAL\Exception
     */
    private function createOrUpdateCategory(
        string $code,
        ?int $id = null,
        ?array $labels = [],
        ?int $parentId = null,
    ): Category {
        $categoryId = (null === $id ? null : new CategoryId($id));
        $parentId = (null === $parentId ? null : new CategoryId($parentId));

        /** @var UpsertCategoryBaseSql $upsertCategoryBaseQuery */
        $upsertCategoryBaseQuery = $this->get(UpsertCategoryBase::class);
        $this->assertEquals(UpsertCategoryBaseSql::class, $upsertCategoryBaseQuery::class);

        /** @var UpsertCategoryTranslationsSql $upsertCategoryTranslationsQuery */
        $upsertCategoryTranslationsQuery = $this->get(UpsertCategoryTranslations::class);
        $this->assertEquals(UpsertCategoryTranslationsSql::class, $upsertCategoryTranslationsQuery::class);

        $categoryModelToCreate = new Category(
            id: $categoryId,
            code: new Code($code),
            labelCollection: LabelCollection::fromArray($labels),
            parentId: $parentId,
        );

        // Insert the category in pim_catalog_category
        $upsertCategoryBaseQuery->execute($categoryModelToCreate);

        // Get the data of the newly inserted category from pim_catalog_category
        $getCategory = $this->get(GetCategorySql::class);
        /** @var Category $categoryBase */
        $categoryBase = $getCategory->byCode((string) $categoryModelToCreate->getCode());
        $parentId = (
            $categoryBase->getParentId() === null
                ? null
                : new CategoryId($categoryBase->getParentId()->getValue())
        )
        ;
        $categoryModelWithId = new Category(
            new CategoryId($categoryBase->getId()->getValue()),
            new Code((string) $categoryBase->getCode()),
            $categoryModelToCreate->getLabelCollection(),
            $parentId,
        );
        $upsertCategoryTranslationsQuery->execute($categoryModelWithId);

        $getCategory = $this->get(GetCategorySql::class);
        $categoryTranslations = $getCategory->byCode((string) $categoryModelToCreate->getCode())->getLabelCollection()->getLabels();

        $createdParentId = (
            $categoryBase->getParentId()?->getValue() > 0
            ? new CategoryId($categoryBase->getParentId()->getValue())
            : null
        );

        // Instantiates a new Category model based on data fetched in database
        return new Category(
            new CategoryId($categoryBase->getId()->getValue()),
            new Code((string) $categoryBase->getCode()),
            LabelCollection::fromArray($categoryTranslations),
            $createdParentId,
        );
    }
}
