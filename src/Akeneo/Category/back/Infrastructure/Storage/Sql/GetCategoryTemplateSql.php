<?php

declare(strict_types=1);

namespace Akeneo\Category\Infrastructure\Storage\Sql;

use Akeneo\Category\Domain\Exception\TemplateNotFoundException;
use Akeneo\Category\Domain\Model\Enrichment\Template;
use Akeneo\Category\Domain\Query\GetTemplate;
use Akeneo\Category\Domain\ValueObject\Template\TemplateUuid;
use Doctrine\DBAL\Connection;

/**
 * @copyright 2022 Akeneo SAS (https://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class GetCategoryTemplateSql implements GetTemplate
{
    public function __construct(private Connection $connection)
    {
    }

    public function byUuid(TemplateUuid $uuid): Template
    {
        $query = <<<SQL
            SELECT
                BIN_TO_UUID(uuid) as uuid,
                code,
                labels,
                category_tree_template.category_tree_id as category_id
            FROM pim_catalog_category_template category_template
            LEFT JOIN pim_catalog_category_tree_template category_tree_template ON category_tree_template.category_template_uuid = category_template.uuid
            WHERE uuid=:template_uuid
            AND (category_template.is_deactivated IS NULL OR category_template.is_deactivated = 0);
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

        if (!$result) {
            throw new TemplateNotFoundException(sprintf('Template with uuid "%s" not found', $uuid));
        }

        return Template::fromDatabase($result);
    }
}
