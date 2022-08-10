<?php

declare(strict_types=1);

namespace Akeneo\Category\Infrastructure\Storage\Save;

use Akeneo\Category\Application\Storage\Save\UpsertCategoryTranslations;
use Akeneo\Category\Domain\Model\Category;
use Doctrine\DBAL\Connection;


/**
 * Save values from model into pim_catalog_category_translation table:
 * The values are inserted if the couple (foreign_key, locale) is new, they are updated if the couple already exists.
 *
 * @copyright 2022 Akeneo SAS (https://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class SqlUpsertCategoryTranslations implements UpsertCategoryTranslations
{
    public function __construct(private Connection $connection)
    {
    }

    public function execute(Category $categoryModel): void
    {
        $values = [];
        foreach ($categoryModel->getLabelCollection() as $locale => $label) {
            $values[] = \sprintf('(%d, %s, %s)', $categoryModel->getId(), $label, $locale);
        }

        // TODO check the ON DUPLICATE : it may only check id
        $query = <<<SQL
            INSERT INTO pim_catalog_category_translation
                (foreign_key, label, locale)
            VALUES
                :values as new_translation
            ON DUPLICATE KEY UPDATE
                foreign_key = new_translation.foreign_key,
                label = new_translation.label,
                locale = new_translation.locale
            ;
        SQL;

        $this->connection->executeQuery(
            $query,
            [
                'values' => \implode(',', $values) // or $values
            ],
            [
                'values' => \PDO::PARAM_STR // or Connection::PARAM_ARRAY_STR
            ]
        );
    }
}
