<?php

declare(strict_types=1);

namespace Akeneo\Category\Infrastructure\Storage\Sql;

use Akeneo\Category\Domain\Query\DeactivatedTemplateAttributeIdentifier;
use Akeneo\Category\Domain\Query\GetDeactivatedTemplateAttributes;
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

    /**
     * @return array<DeactivatedTemplateAttributeIdentifier>
     *
     * @throws \Doctrine\DBAL\Driver\Exception
     * @throws \Doctrine\DBAL\Exception
     */
    public function execute(): array
    {
        $sql = <<<SQL
            SELECT BIN_TO_UUID(uuid) AS uuid, code
            FROM pim_catalog_category_attribute
            WHERE is_deactivated = 1
        SQL;
        $results = $this->connection->executeQuery($sql)->fetchAllAssociative();

        $deactivatedTemplateAttributeList = [];
        foreach ($results as $result) {
            $deactivatedTemplateAttributeList[] = new DeactivatedTemplateAttributeIdentifier(
                $result['uuid'],
                $result['code'],
            );
        }

        return $deactivatedTemplateAttributeList;
    }
}
