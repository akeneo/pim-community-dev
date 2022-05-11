<?php

declare(strict_types=1);

namespace Pim\Upgrade\Schema\Tests;

use Akeneo\Test\Integration\Configuration;
use Akeneo\Test\Integration\TestCase;
use Doctrine\DBAL\Connection;

class Version_6_0_20220509143200_use_the_new_process_tracker_route_Integration extends TestCase
{
    use ExecuteMigrationTrait;

    private const MIGRATION_LABEL = '_6_0_20220509143200_use_the_new_process_tracker_route';

    public function test_it_replaces_old_import_export_routes(): void
    {
        $this->addNotifications();

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

    private function addNotifications(): void
    {
        $this->getConnection()->executeStatement(<<<SQL
            INSERT INTO pim_notification_notification (route, routeParams, message, messageParams, comment, created, type, context)
            VALUES ('pim_importexport_import_execution_show', 'a:1:{s:2:"id";i:609;}', 'pim_import_export.notification.export.success', 'a:1:{s:7:"%label%";s:17:"Export Kategorien";}', NULL, '2019-11-29 13:38:51', 'success', 'a:1:{s:10:"actionType";s:6:"export";}');
        SQL);

        $this->getConnection()->executeStatement(<<<SQL
            INSERT INTO pim_notification_notification (route, routeParams, message, messageParams, comment, created, type, context)
            VALUES ('pim_importexport_export_execution_show', 'a:1:{s:2:"id";i:607;}', 'pim_import_export.notification.import.warning', 'a:1:{s:7:"%label%";s:10:"Kategorien";}', NULL, '2019-11-29 13:18:07', 'warning', 'a:1:{s:10:"actionType";s:6:"import";}');
        SQL);
    }

    private function notificationTableContainOldProcessTrackerRoute(): bool
    {
        $query = <<<SQL
SELECT *
FROM pim_notification_notification
WHERE route IN('pim_importexport_export_execution_show', 'pim_importexport_import_execution_show')
SQL;

        return !empty($this->getConnection()->fetchAllAssociative($query));
    }
}
