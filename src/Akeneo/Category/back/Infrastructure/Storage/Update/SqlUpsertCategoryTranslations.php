<?php

declare(strict_types=1);

namespace Akeneo\Category\Infrastructure\Storage\Update;

use Akeneo\Category\Application\Storage\Update\UpsertCategoryTranslations;
use Akeneo\Category\Domain\Model\Category;
use Doctrine\DBAL\Connection;


/**
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

        $query = <<<SQL
            INSERT INTO pim_catalog_category_translation
                (foreign_key, label, locale)
            VALUES
                :values as new_translaction
            ON DUPLICATE KEY UPDATE
                foreign_key = new_translaction.foreign_key,
                label = new_translaction.label,
                locale = new_translaction.locale
            ;
        SQL;

        $this->connection->executeQuery(
            $query,
            [
                'values' => \implode(',', $values)
            ],
            [
                'values' => \PDO::PARAM_STR
            ]
        );
    }
}
