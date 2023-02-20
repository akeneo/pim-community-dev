<?php

declare(strict_types=1);

namespace Akeneo\Category\Infrastructure\Storage\Sql;

use Akeneo\Category\Application\Query\GetCategoryTreeByCategoryTemplate;
use Akeneo\Category\Domain\Model\Classification\CategoryTree;
use Akeneo\Category\Domain\Model\Enrichment\Category;
use Akeneo\Category\Domain\ValueObject\Template\TemplateUuid;
use Doctrine\DBAL\Connection;

/**
 * @copyright 2022 Akeneo SAS (https://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class GetCategoryTreeByCategoryTemplateSql implements GetCategoryTreeByCategoryTemplate
{
    public function __construct(private Connection $connection)
    {
    }

    /**
     * @return ?Category
     *
     * @throws \Doctrine\DBAL\Driver\Exception
     * @throws \Doctrine\DBAL\Exception
     */
    public function __invoke(TemplateUuid $templateUuid): ?CategoryTree
    {
        $query = <<< SQL
            WITH translation as (
                SELECT category.code, JSON_OBJECTAGG(translation.locale, translation.label) as translations
                FROM pim_catalog_category category
                JOIN pim_catalog_category_translation translation ON translation.foreign_key = category.id
                GROUP BY code
            )
            SELECT
                category.id AS id,
                category.code AS code,
                translation.translations AS translations,
                BIN_TO_UUID(category_template.uuid) AS template_uuid,
                category_template.labels AS template_labels,
                category_template.code AS template_code            
            FROM pim_catalog_category category
                LEFT JOIN pim_catalog_category_tree_template category_tree_template
                     ON category_tree_template.category_tree_id=category.id
                LEFT JOIN pim_catalog_category_template category_template
                    ON category_template.uuid=category_tree_template.category_template_uuid AND (category_template.is_deactivated IS NULL OR category_template.is_deactivated = 0)
                LEFT JOIN translation 
                     ON category.code = translation.code
            WHERE category_template_uuid=:template_uuid
            AND category.parent_id IS NULL
        SQL;

        $result = $this->connection->executeQuery(
            $query,
            [
                'template_uuid' => $templateUuid->toBytes(),
            ],
            [
                'template_uuid' => \PDO::PARAM_STR,
            ],
        )->fetchAssociative();

        if (!$result) {
            return null;
        }

        return CategoryTree::fromDatabase($result);
    }
}
