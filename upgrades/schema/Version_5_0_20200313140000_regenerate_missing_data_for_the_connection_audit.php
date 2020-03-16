<?php

declare(strict_types=1);

namespace Pim\Upgrade\Schema;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\DBAL\Types\Types;
use Doctrine\Migrations\AbstractMigration;

/**
 * @copyright 2020 Akeneo SAS (http://www.akeneo.com)
 * @license http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
final class Version_5_0_20200313140000_regenerate_missing_data_for_the_connection_audit extends AbstractMigration
{
    public function up(Schema $schema): void
    {
        $auditDataExists = $this->connection->executeQuery('SELECT COUNT(*) FROM akeneo_connectivity_connection_audit_product')
            ->fetchColumn();
        if ($auditDataExists > 0) {
            return;
        }

        $selectEventCountByTime = <<<SQL
SELECT conn.code, versioning.version != 1 as is_updated, count(versioning.version) as total
FROM pim_versioning_version versioning USE INDEX(logged_at_idx)
INNER JOIN oro_user u ON u.username = versioning.author AND u.user_type = 'api'
INNER JOIN akeneo_connectivity_connection conn ON conn.user_id = u.id
WHERE resource_name = 'Akeneo\\Pim\\Enrichment\\Component\\Product\\Model\\Product'
AND logged_at >= :start_time AND logged_at < :end_time
GROUP BY conn.code, is_updated
SQL;

        $now = new \DateTimeImmutable('now', new \DateTimeZone('UTC'));
        $now = $now->setTime((int) $now->format('H'), 0);
        $tenDaysAgo = $now->modify('- 10 days');

        $period = new \DatePeriod(
            $tenDaysAgo,
            new \DateInterval('PT1H'),
            $now->modify('+ 3 hour')
        );

        foreach ($period as $endDateTime) {
            $result = $this->connection->executeQuery(
                $selectEventCountByTime,
                [
                    'start_time' => $endDateTime->sub(new \DateInterval('PT1H')),
                    'end_time' => $endDateTime
                ],
                [
                    'start_time' => Types::DATETIME_IMMUTABLE,
                    'end_time' => Types::DATETIME_IMMUTABLE,
                ]
            )->fetchAll();

            dump($result);
        }
    }

    public function down(Schema $schema): void
    {
        $this->throwIrreversibleMigrationException();
    }
}
