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
    public function byUuid(TemplateUuid $templateUuid): ?Template
    {
        $query = <<< SQL
            SELECT
                BIN_TO_UUID(uuid) as uuid,
                code,
                labels
            FROM pim_catalog_category_template category_template
            WHERE uuid=:template_uuid;
        SQL;

        $result = $this->connection->executeQuery(
            $query,
            [
                'template_uuid' => $templateUuid->toBytes()
            ],
            [
                'template_uuid' => \PDO::PARAM_STR
            ]
        )->fetchAssociative();

        $category = null;

        if ($result) {
            $category = Template::fromDatabase($result);
        }

        return $category;
    }
}
