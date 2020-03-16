<?php declare(strict_types=1);

namespace Pim\Upgrade\Schema;

use Akeneo\Connectivity\Connection\Domain\Settings\Model\Read\ConnectionWithCredentials;
use Akeneo\Connectivity\Connection\Domain\Settings\Model\ValueObject\FlowType;
use Akeneo\Test\Integration\TestCase;
use Doctrine\DBAL\Connection;
use Pim\Upgrade\Schema\Tests\ExecuteMigrationTrait;

final class Version_5_0_20200313140000_regenerate_missing_data_for_the_connection_audit_Integration extends TestCase
{
    use ExecuteMigrationTrait;

    private const MIGRATION_LABEL = '_5_0_20200313140000_regenerate_missing_data_for_the_connection_audit';

    /** @var Connection */
    private $dbalConnection;

    protected function getConfiguration()
    {
        return $this->catalog->useMinimalCatalog();
    }

    protected function setUp(): void
    {
        parent::setUp();

        $this->dbalConnection = $this->get('database_connection');
    }

    public function test_it_does_not_calculate_anything_if_table_is_already_filled(): void
    {
        $this->ensureAuditProductTableIsFilled();
        $this->reExecuteMigration(self::MIGRATION_LABEL);

        $this->assertAuditProductTableEntryCount(1);
    }

    public function test_it_recalculate_audit_for_the_last_10_days(): void
    {
        $this->ensureAuditProductTableIsEmpty();
        $connection = $this->createConnection('franklin');
        $connection2 = $this->createConnection('cnet');

        $yesterdayDateTime = new \DateTime('yesterday', new \DateTimeZone('UTC'));
        $this->insertVersionRow($connection->username(), true, $yesterdayDateTime->format('Y-m-d H:i:s'));
        $this->insertVersionRow($connection->username(), false, $yesterdayDateTime->modify('+1 hour')->format('Y-m-d H:i:s'));
        $this->insertVersionRow($connection->username(), false, $yesterdayDateTime->modify('+1 hour')->format('Y-m-d H:i:s'));

        $nowDateTime = new \DateTime('now', new \DateTimeZone('UTC'));
        $this->insertVersionRow($connection->username(), false, $nowDateTime->format('Y-m-d H:i:s'));
        $this->insertVersionRow($connection2->username(), true, $nowDateTime->format('Y-m-d H:i:s'));
        $this->insertVersionRow($connection2->username(), false, $nowDateTime->format('Y-m-d H:i:s'));

        $this->reExecuteMigration(self::MIGRATION_LABEL);

        $this->assertAuditProductTableEntryCount(648);
    }

    //TODO: Test if no connections

    private function ensureAuditProductTableIsFilled(): void
    {
        $insertAuditProductRowSql = <<<SQL
INSERT INTO akeneo_connectivity_connection_audit_product
VALUES ('<all>', '2020-03-20 14:00:00', 12, 'product_created', '2020-03-21 00:02:01')
SQL;
        $this->dbalConnection->executeQuery($insertAuditProductRowSql);
    }

    private function ensureAuditProductTableIsEmpty(): void
    {
        $this->dbalConnection->executeQuery('DELETE FROM akeneo_connectivity_connection_audit_product');
        $this->assertAuditProductTableEntryCount(0);
    }

    private function assertAuditProductTableEntryCount(int $expectedCount): void
    {
        $stmt = $this->dbalConnection->executeQuery('SELECT COUNT(1) FROM akeneo_connectivity_connection_audit_product');
        $this->assertEquals($expectedCount, $stmt->fetchColumn());
    }

    private function insertVersionRow(string $userApi, int $resourceId, bool $created, $loggedAt): void
    {
        $insertVersioningSql = <<<SQL
INSERT INTO pim_versioning_version (author, resource_name, resource_id, version, logged_at, changeset, pending)
VALUES (:user_api, 'Akeneo\\\\Pim\\\\Enrichment\\\\Component\\\\Product\\\\Model\\\\Product', :resource_id, :version, :logged_at, '{}', 0);
SQL;
        $insertParams = [
            'user_api' => $userApi,
            'version' => ($created) ? 1 : 2,
            'logged_at' => $loggedAt

        ];
        $this->dbalConnection->executeQuery($insertVersioningSql, $insertParams);
    }

    private function createConnection($connectionCode): ConnectionWithCredentials
    {
        return $this
            ->get('akeneo_connectivity.connection.fixtures.connection_loader')
            ->createConnection($connectionCode, $connectionCode, FlowType::DATA_SOURCE);
    }
}
