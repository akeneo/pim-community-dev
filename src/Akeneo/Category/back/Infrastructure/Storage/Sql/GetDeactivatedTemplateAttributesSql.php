<?php

declare(strict_types=1);

namespace Akeneo\Category\Infrastructure\Storage\Sql;

use Akeneo\Category\Domain\Query\GetDeactivatedTemplateAttributes;
use Akeneo\Category\Domain\ValueObject\Attribute\Value\AbstractValue;
use Akeneo\Category\Domain\ValueObject\Template\TemplateUuid;
use Doctrine\DBAL\Connection;

/**
 * @copyright 2023 Akeneo SAS (https://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class GetDeactivatedTemplateAttributesSql implements GetDeactivatedTemplateAttributes
{
    public function __construct(private readonly Connection $connection)
    {
    }

    public function execute(): array
    {
        $sql = <<<SQL
            SELECT BIN_TO_UUID(uuid) AS attribute_uuid
            FROM pim_catalog_category_attribute
            WHERE is_deactivated = 1
        SQL;
        return $this->connection->executeQuery($sql)->fetchAllAssociativeIndexed();
    }
}
