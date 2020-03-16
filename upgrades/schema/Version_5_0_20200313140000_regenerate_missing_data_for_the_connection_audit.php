<?php

declare(strict_types=1);

namespace Pim\Upgrade\Schema;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;
use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * @copyright 2020 Akeneo SAS (http://www.akeneo.com)
 * @license http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
final class Version_5_0_20200313140000_regenerate_missing_data_for_the_connection_audit extends AbstractMigration implements ContainerAwareInterface
{
    /** @var ContainerInterface */
    private $container;

    public function up(Schema $schema): void
    {
        $auditDataExists = $this->connection->executeQuery('SELECT COUNT(1) FROM akeneo_connectivity_connection_audit_product')->fetchColumn();
        if ($auditDataExists > 0) {
            return;
        }

        $datetimeUTC = new \DateTime('now', new \DateTimeZone('UTC'));
        $datetimeUTC->setTime((int) $datetimeUTC->format('H'), 0);

        $hourInterval = new \DateInterval('PT1H');

        for ($i = 24*9; $i > 0; $i--) {
            $this->recalculateForDateTime($datetimeUTC->sub($hourInterval));
        }
    }

    private function recalculateForDateTime(\DateTime $startDateTime): void
    {
        $endDateTime = \DateTime::createFromFormat('Y-m-d H:i:s', $startDateTime->format('Y-m-d H:i:s'), new \DateTimeZone('UTC'));
        $endDateTime->setTime((int) $endDateTime->format('H')+1, 0, 0);

        // Recalculate created
        $this->recalculateCreatedForDateTime($startDateTime, $endDateTime);
        // Recalculate updated
        $this->recalculateUpdatedForDateTime($startDateTime, $endDateTime);
    }

    private function recalculateCreatedForDateTime(\DateTime $startDateTime, \DateTime $endDateTime): void
    {
        $selectEventCountByTime = <<<SQL
SELECT conn.code AS connection_code, COUNT(resource_id) as event_count
FROM (
    SELECT author, resource_id
    FROM pim_versioning_version USE INDEX(logged_at_idx)
    WHERE logged_at >= :start_time AND logged_at < :end_time
    AND resource_name = :resource_name
    AND version = 1
    GROUP BY author, resource_id
) AS tmp_table
INNER JOIN oro_user u ON u.username = author AND u.user_type = 'api'
INNER JOIN akeneo_connectivity_connection conn ON conn.user_id = u.id
GROUP BY conn.code;
SQL;

        $dateTimeParams = [
            'start_time' => $startDateTime->format('Y-m-d H:i:s'),
            'end_time'   => $endDateTime->format('Y-m-d H:i:s'),
            'resource_name' => $this->container->getParameter('pim_catalog.entity.product.class'),
        ];
        $eventCounts = $this->connection->executeQuery($selectEventCountByTime, $dateTimeParams)->fetchAll();

        $totalCount = 0;
        foreach ($eventCounts as $eventCount) {
            $this->insertAuditProductRow($eventCount['connection_code'], (int) $eventCount['event_count'], $startDateTime->format('Y-m-d H:i:s'), 'product_created');
            $totalCount += (int) $eventCount['event_count'];
        }
        $this->insertAuditProductRow('<all>', (int) $totalCount, $startDateTime->format('Y-m-d H:i:s'), 'product_created');
    }

    private function recalculateUpdatedForDateTime(\DateTime $startDateTime, \DateTime $endDateTime): void
    {
        $selectEventCountByTime = <<<SQL
SELECT conn.code AS connection_code, COUNT(resource_id) as event_count
FROM (
    SELECT author, resource_id
    FROM pim_versioning_version USE INDEX(logged_at_idx)
    WHERE logged_at >= :start_time AND logged_at < :end_time
    AND resource_name = :resource_name
    AND version != 1
    GROUP BY author, resource_id
) AS tmp_table
INNER JOIN oro_user u ON u.username = author AND u.user_type = 'api'
INNER JOIN akeneo_connectivity_connection conn ON conn.user_id = u.id
GROUP BY conn.code;
SQL;

        $dateTimeParams = [
            'start_time' => $startDateTime->format('Y-m-d H:i:s'),
            'end_time'   => $endDateTime->format('Y-m-d H:i:s'),
            'resource_name' => $this->container->getParameter('pim_catalog.entity.product.class'),
        ];
        $eventCounts = $this->connection->executeQuery($selectEventCountByTime, $dateTimeParams)->fetchAll();

        $totalCount = 0;
        foreach ($eventCounts as $eventCount) {
            $this->insertAuditProductRow($eventCount['connection_code'], (int) $eventCount['event_count'], $startDateTime->format('Y-m-d H:i:s'), 'product_updated');
            $totalCount += (int) $eventCount['event_count'];
        }
        $this->insertAuditProductRow('<all>', (int) $totalCount, $startDateTime->format('Y-m-d H:i:s'), 'product_updated');
    }

    private function insertAuditProductRow(string $connectionCode, int $eventCount, string $eventDateTime, string $eventType): void
    {
        $insertQuerySql = <<<SQL
INSERT INTO akeneo_connectivity_connection_audit_product (connection_code, event_datetime, event_count, event_type, updated)
VALUES(:connection_code, :event_datetime, :event_count, :event_type, UTC_TIMESTAMP())
SQL;
        $insertQueryParams = [
            'connection_code' => $connectionCode,
            'event_datetime'  => $eventDateTime,
            'event_count'     => $eventCount,
            'event_type'      => $eventType
        ];
        $this->connection->executeQuery($insertQuerySql, $insertQueryParams);
    }

    public function down(Schema $schema): void
    {
        $this->throwIrreversibleMigrationException();
    }

    /**
     * @inheritDoc
     */
    public function setContainer(ContainerInterface $container = null)
    {
        $this->container = $container;
    }
}
