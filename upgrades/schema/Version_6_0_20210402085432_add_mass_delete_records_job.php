<?php

declare(strict_types=1);

namespace Pim\Upgrade\Schema;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version_6_0_20210402085432_add_mass_delete_records_job extends AbstractMigration
{
    public function up(Schema $schema): void
    {
        $this->addSql(
            <<<SQL
        INSERT INTO `akeneo_batch_job_instance` (
            `code`,
            `label`,
            `job_name`,
            `status`,
            `connector`,
            `raw_parameters`,
            `type`
        ) VALUES (
            'reference_entity_mass_delete_records',
            'Mass delete records',
            'reference_entity_mass_delete_records',
            0,
            'internal',
            'a:0:{}',
            'reference_entity_mass_delete_records'
        );
SQL
        );
    }

    public function down(Schema $schema): void
    {
        $this->throwIrreversibleMigrationException();
    }
}
