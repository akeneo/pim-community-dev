<?php

declare(strict_types=1);

namespace Pim\Upgrade\Schema\Tests;

use Akeneo\Test\Integration\Configuration;
use Akeneo\Test\Integration\TestCase;
use Doctrine\DBAL\Connection;
use PHPUnit\Framework\Assert;

class Version_6_0_20211217145100_use_the_new_process_tracker_route_Integration extends TestCase
{
    use ExecuteMigrationTrait;

    private const MIGRATION_LABEL = '_6_0_20211217145100_use_the_new_process_tracker_route';

    public function test_it_adds_a_default_role_type_to_oro_access_role(): void
    {
        $this->addNotification();

        $this->assertTrue($this->notificationTableContainOldProcessTrackerRoute());
        $this->reExecuteMigration(self::MIGRATION_LABEL);
        $this->assertFalse($this->notificationTableContainOldProcessTrackerRoute());
    }

    protected function getConfiguration(): Configuration
    {
        return $this->catalog->useMinimalCatalog();
    }

    private function getConnection(): Connection
    {
        return $this->get('database_connection');
    }

    private function addNotification(): void
    {
        $this->getConnection()->executeStatement(<<<SQL
            INSERT INTO pim_notification_notification (route, routeParams, message, messageParams, comment, created, type, context)
            VALUES ('pim_enrich_job_tracker_show', 'a:1:{s:2:"id";i:21;}', 'pim_mass_edit.notification.mass_edit.error', 'a:1:{s:7:"%label%";s:28:"Mass edit product attributes";}', NULL, '2021-12-15 14:59:31', 'error', 'a:1:{s:10:"actionType";s:9:"mass_edit";}');
        SQL);
    }

    private function notificationTableContainOldProcessTrackerRoute(): bool
    {
        $query = <<<SQL
SELECT *
FROM pim_notification_notification
WHERE route = 'pim_enrich_job_tracker_show'
SQL;

        return !empty($this->getConnection()->fetchAllAssociative($query));
    }
}
