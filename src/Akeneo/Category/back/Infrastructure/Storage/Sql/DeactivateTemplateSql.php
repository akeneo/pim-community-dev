<?php

declare(strict_types=1);

namespace Akeneo\Category\Infrastructure\Storage\Sql;

use Akeneo\Category\Application\Query\DeactivateTemplate;
use Akeneo\Category\Domain\ValueObject\Template\TemplateUuid;
use Doctrine\DBAL\Connection;

/**
 * @copyright 2023 Akeneo SAS (https://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class DeactivateTemplateSql implements DeactivateTemplate
{
    public function __construct(private readonly Connection $connection)
    {
    }

    public function execute(TemplateUuid $uuid): void
    {
        $query = <<< SQL
            UPDATE pim_catalog_category_template 
            SET is_deactivated = true
            WHERE uuid = :template_uuid
        SQL;

        $this->connection->executeQuery(
            $query,
            ['template_uuid' => $uuid->toBytes()],
            ['template_uuid' => \PDO::PARAM_STR],
        );
    }
}
