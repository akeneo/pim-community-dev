<?php

declare(strict_types=1);

namespace Akeneo\Category\Infrastructure\Storage\Sql;

use Akeneo\Category\Application\Query\GetTemplate;
use Akeneo\Category\Domain\Model\Enrichment\Template;
use Akeneo\Category\Domain\ValueObject\Template\TemplateUuid;
use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Driver\Exception;

/**
 * @copyright 2022 Akeneo SAS (https://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class GetCategoryTemplateSql implements GetTemplate
{
    public function __construct(private Connection $connection)
    {
    }

    /**
     * @throws Exception
     * @throws \JsonException
     * @throws \Doctrine\DBAL\Exception
     */
    public function byUuid(TemplateUuid $uuid): ?Template
    {
        $query = <<< SQL
            SELECT
                BIN_TO_UUID(uuid) as uuid,
                code,
                labels,
                category_tree_template.category_tree_id as category_id 
            FROM pim_catalog_category_template category_template
            INNER JOIN pim_catalog_category_tree_template category_tree_template ON category_tree_template.category_template_uuid = category_template.uuid
            WHERE uuid=:template_uuid;
        SQL;

        $result = $this->connection->executeQuery(
            $query,
            [
                'template_uuid' => $uuid->toBytes(),
            ],
            [
                'template_uuid' => \PDO::PARAM_STR,
            ],
        )->fetchAssociative();

        $template = null;

        if ($result) {
            $template = Template::fromDatabase($result);
        }

        return $template;
    }
}
