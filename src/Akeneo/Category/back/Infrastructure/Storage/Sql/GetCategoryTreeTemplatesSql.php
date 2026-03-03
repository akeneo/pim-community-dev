<?php

declare(strict_types=1);

namespace Akeneo\Category\Infrastructure\Storage\Sql;

use Akeneo\Category\Domain\Query\GetCategoryTreeTemplates;
use Akeneo\Category\Domain\ValueObject\CategoryId;
use Akeneo\Category\Domain\ValueObject\Template\TemplateUuid;
use Doctrine\DBAL\Connection;

/**
 * @copyright 2022 Akeneo SAS (https://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class GetCategoryTreeTemplatesSql implements GetCategoryTreeTemplates
{
    public function __construct(private readonly Connection $connection)
    {
    }

    public function __invoke(CategoryId $categoryTreeId): array
    {
        $result = $this->connection->executeQuery(
            <<<SQL
                SELECT BIN_TO_UUID(category_template_uuid) as category_template_uuid
                FROM pim_catalog_category_tree_template
                WHERE category_tree_id = :category_tree_id
            SQL,
            [
                'category_tree_id' => $categoryTreeId->getValue(),
            ],
            [
                'category_tree_id' => \PDO::PARAM_INT,
            ],
        );

        return array_map(
            fn (string $uuid) => TemplateUuid::fromString($uuid),
            $result->fetchFirstColumn(),
        );
    }
}
