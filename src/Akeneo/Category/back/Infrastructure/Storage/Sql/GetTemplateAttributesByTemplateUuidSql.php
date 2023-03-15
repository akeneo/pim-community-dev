<?php

declare(strict_types=1);

namespace Akeneo\Category\Infrastructure\Storage\Sql;

use Akeneo\Category\Application\Query\GetTemplateAttributesByTemplateUuid;
use Akeneo\Category\Domain\ValueObject\Template\TemplateUuid;
use Doctrine\DBAL\Connection;

/**
 * @copyright 2023 Akeneo SAS (https://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class GetTemplateAttributesByTemplateUuidSql implements GetTemplateAttributesByTemplateUuid
{
    public function __construct(private readonly Connection $connection)
    {
    }

    public function execute(string $templateUuid): array
    {
        $query = <<<SQL
            SELECT BIN_TO_UUID(attribute.uuid), attribute.code
            FROM pim_catalog_category_attribute AS attribute
            WHERE attribute.category_template_uuid = :template_uuid
        SQL;

        return $this->connection->executeQuery(
            $query,
            ['template_uuid' => TemplateUuid::fromString($templateUuid)->toBytes()],
            ['template_uuid' => \PDO::PARAM_STR],
        )->fetchAllKeyValue();
    }
}
