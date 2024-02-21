<?php

declare(strict_types=1);

namespace Pim\Upgrade\Schema;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version_6_0_20220509143200_use_the_new_process_tracker_route extends AbstractMigration
{
    public function up(Schema $schema): void
    {
        $this->addSql('UPDATE pim_notification_notification SET route = "akeneo_job_process_tracker_details" WHERE route IN ("pim_importexport_export_execution_show", "pim_importexport_import_execution_show")');
    }

    public function down(Schema $schema): void
    {
        $this->throwIrreversibleMigrationException();
    }
}
