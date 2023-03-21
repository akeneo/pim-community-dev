<?php

declare(strict_types=1);

namespace Akeneo\Category\Infrastructure\Storage\Sql;

use Akeneo\Category\Application\Query\DeleteCategoryTreeTemplate;
use Akeneo\Category\Domain\ValueObject\CategoryId;
use Akeneo\Category\Domain\ValueObject\Template\TemplateUuid;
use Doctrine\DBAL\Connection;

/**
 * @copyright 2022 Akeneo SAS (https://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class DeleteCategoryTreeTemplateSql implements DeleteCategoryTreeTemplate
{
    public function __construct(private readonly Connection $connection)
    {
    }

    public function byCategoryIdAndTemplateUuid(CategoryId $categoryTreeId, TemplateUuid $templateUuid): void
    {
        $query = <<< SQL
            DELETE FROM pim_catalog_category_tree_template
            WHERE category_tree_id = :category_tree_id
            AND category_template_uuid = :template_uuid
        SQL;

        $this->connection->executeQuery(
            $query,
            [
                'category_tree_id' => $categoryTreeId->getValue(),
                'template_uuid' => $templateUuid->toBytes(),
            ],
            [
                'category_tree_id' => \PDO::PARAM_INT,
                'template_uuid' => \PDO::PARAM_STR,
            ],
        );
    }

    public function byTemplateUuid(TemplateUuid $templateUuid): void
    {
        $query = <<< SQL
            DELETE FROM pim_catalog_category_tree_template
            WHERE category_template_uuid = :template_uuid
        SQL;

        $this->connection->executeQuery(
            $query,
            [
                'template_uuid' => $templateUuid->toBytes(),
            ],
            [
                'template_uuid' => \PDO::PARAM_STR,
            ],
        );
    }
}
