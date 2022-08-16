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
use Akeneo\Category\Infrastructure\Storage\Save\Query\SqlUpsertCategoryBase;
use Akeneo\Category\Infrastructure\Storage\Save\Query\SqlUpsertCategoryTranslations;
use Doctrine\DBAL\Connection;

trait CategoryTrait
{
    private function createOrUpdateCategory(
        string $code,
        ?int $id = null,
        ?array $labels = [],
        ?int $parentId = null
    ): Category
    {
        $categoryId = (null === $id ? null : new CategoryId($id));
        $parentId = (null === $parentId ? null : new CategoryId($parentId));

        /** @var SqlUpsertCategoryBase $upsertCategoryBaseQuery */
        $upsertCategoryBaseQuery = $this->get(UpsertCategoryBase::class);
        $this->assertEquals(SqlUpsertCategoryBase::class, $upsertCategoryBaseQuery::class);

        /** @var SqlUpsertCategoryTranslations $upsertCategoryTranslationsQuery */
        $upsertCategoryTranslationsQuery = $this->get(UpsertCategoryTranslations::class);
        $this->assertEquals(SqlUpsertCategoryTranslations::class, $upsertCategoryTranslationsQuery::class);

        $categoryModelToCreate = new Category(
            id: $categoryId,
            code: new Code($code),
            labelCollection: LabelCollection::fromArray($labels),
            parentId: $parentId
        );

        // Insert the category in pim_catalog_category
        $upsertCategoryBaseQuery->execute($categoryModelToCreate);

        // Get the data of the newly inserted category from pim_catalog_category
        $categoryBaseData = $this->getCategoryBaseDataByCode((string) $categoryModelToCreate->getCode());

        $parentId = (
            null === $categoryBaseData['parent_id'] ?
                null
                : new CategoryId((int) $categoryBaseData['parent_id']))
        ;
        $categoryModelWithId = new Category(
            new CategoryId((int) $categoryBaseData['id']),
            new Code($categoryBaseData['code']),
            $categoryModelToCreate->getLabelCollection(),
            $parentId
        );
        $upsertCategoryTranslationsQuery->execute($categoryModelWithId);

        $categoryTranslationsData = $this->getCategoryTranslationsDataByCategoryCode((string) $categoryModelToCreate->getCode());

        $createdParentId = ($categoryBaseData['parent_id'] > 0 ?
            new CategoryId((int) $categoryBaseData['parent_id'])
            : null
        );

        // Instantiate a new Category model based on data fetched in database
        return new Category(
            new CategoryId((int) $categoryBaseData['id']),
            new Code($categoryBaseData['code']),
            LabelCollection::fromArray($categoryTranslationsData),
            $createdParentId
        );
    }

    /**
     * @param int $id
     * @return array<string, string>
     * @throws \Doctrine\DBAL\Driver\Exception
     * @throws \Doctrine\DBAL\Exception
     */
    private function getCategoryBaseDataByCode(string $code): array
    {
        // TODO use dedicated GetCategory class when checking 'root' properties in Category model will be possible
        /** @var Connection $connection */
        $connection = $this->get('database_connection');

        $query = <<< SQL
            SELECT * 
            FROM pim_catalog_category
            WHERE code=:category_code
        SQL;

        return $connection->executeQuery(
            $query,
            [
                'category_code' => $code,
            ],
            [
                'category_code' => \PDO::PARAM_STR,
            ]
        )->fetchAssociative();
    }

    /**
     * @param int $id
     * @return array<string, string>
     * @throws \Doctrine\DBAL\Driver\Exception
     * @throws \Doctrine\DBAL\Exception
     */
    private function getCategoryTranslationsDataByCategoryCode(string $code): array
    {
        // TODO use dedicated GetCategory class when checking 'root' properties in Category model will be possible
        /** @var Connection $connection */
        $connection = $this->get('database_connection');

        $query = <<< SQL
            SELECT
                code,
                JSON_OBJECTAGG(translation.locale, translation.label) as translations
            FROM pim_catalog_category_translation translation
            JOIN pim_catalog_category category ON translation.foreign_key = category.id
            WHERE  code=:category_code
        SQL;

        $result = $connection->executeQuery(
            $query,
            [
                'category_code' => $code,
            ],
            [
                'category_code' => \PDO::PARAM_STR,
            ]
        )->fetchAssociative();

        if (false === $result || empty($result['translations'])) {
            return [];
        }

        return json_decode($result['translations'], true);
    }
}
