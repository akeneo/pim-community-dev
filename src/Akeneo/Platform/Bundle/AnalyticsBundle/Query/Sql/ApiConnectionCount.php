<?php

declare(strict_types=1);

namespace Akeneo\Platform\Bundle\AnalyticsBundle\Query\Sql;

use Akeneo\Connectivity\Connection\Domain\Settings\Model\ValueObject\FlowType;
use Akeneo\Tool\Component\Analytics\ApiConnectionCountQuery;
use Doctrine\DBAL\Connection;
use Doctrine\DBAL\FetchMode;

/**
 * @author Pierre Jolly <pierre.jolly@akeneo.com>
 * @copyright 2020 Akeneo SAS (http://www.akeneo.com)
 * @license http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class ApiConnectionCount implements ApiConnectionCountQuery
{
    /** @var Connection */
    private $connection;

    public function __construct(Connection $connection)
    {
        $this->connection = $connection;
    }

    public function fetch(): array
    {
        $query = <<<SQL
SELECT flow_type, count(flow_type) AS "flow_type_count", auditable
FROM akeneo_connectivity_connection
GROUP BY flow_type, auditable
SQL;

        $data = $this->connection->executeQuery($query)->fetchAllAssociative();

        return $this->normalize($data);
    }

    private function normalize(array $data): array
    {
        $normalized[FlowType::DATA_SOURCE] = ['tracked' => 0, 'untracked' => 0];
        $normalized[FlowType::DATA_DESTINATION] = ['tracked' => 0, 'untracked' => 0];
        $normalized[FlowType::OTHER] = ['tracked' => 0, 'untracked' => 0];

        foreach ($data as $row) {
            $status = (bool) $row['auditable'] ? 'tracked':'untracked';
            $normalized[$row['flow_type']][$status] = $row['flow_type_count'];
        }

        return $normalized;
    }
}
