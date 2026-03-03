<?php

declare(strict_types=1);

namespace Akeneo\Category\Infrastructure\Storage\Sql;

use Akeneo\Category\Application\Query\GetCategoryTemplateByCategoryTree;
use Akeneo\Category\Domain\Model\Enrichment\Template;
use Akeneo\Category\Domain\ValueObject\CategoryId;
use Doctrine\DBAL\Connection;

/**
 * @copyright 2022 Akeneo SAS (https://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class GetCategoryTemplateByCategoryTreeSql implements GetCategoryTemplateByCategoryTree
{
    public function __construct(private Connection $connection)
    {
    }

    /**
     * @return ?Template
     *
     * @throws \Doctrine\DBAL\Driver\Exception
     * @throws \Doctrine\DBAL\Exception
     * @throws \JsonException
     */
    public function __invoke(CategoryId $categoryTreeId): ?Template
    {
        $query = <<<SQL
            SELECT 
                BIN_TO_UUID(category_template.uuid) as uuid,
                category_template.code,
                category_template.labels,
                category_tree_template.category_tree_id as category_id
            FROM pim_catalog_category_template category_template
            JOIN pim_catalog_category_tree_template category_tree_template 
                ON category_tree_template.category_template_uuid = category_template.uuid
            WHERE category_tree_template.category_tree_id = :category_id
            AND (category_template.is_deactivated IS NULL OR category_template.is_deactivated = 0)
        SQL;

        $result = $this->connection->executeQuery(
            $query,
            ['category_id' => $categoryTreeId->getValue()],
            ['category_id' => \PDO::PARAM_INT],
        )->fetchAssociative();

        if (!$result) {
            return null;
        }

        return Template::fromDatabase($result);
    }
}
