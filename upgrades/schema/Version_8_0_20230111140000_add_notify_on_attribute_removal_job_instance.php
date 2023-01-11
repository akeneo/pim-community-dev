<?php

declare(strict_types=1);

namespace Pim\Upgrade\Schema;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version_8_0_20230111140000_add_notify_on_attribute_removal_job_instance extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Adds the notify_on_attribute_removal job instance';
    }

    public function up(Schema $schema): void
    {
        $this->addSql(
            <<<SQL
            INSERT INTO akeneo_batch_job_instance (code, label, job_name, status, connector, raw_parameters, type)
            VALUES (:code, :label, :job_name, :status, :connector, :raw_parameters, :type)
            ON DUPLICATE KEY UPDATE code = code;
            SQL,
            [
                'code' => 'notify_on_attribute_removal',
                'label' => 'Notify on attribute removal',
                'job_name' => 'notify_on_attribute_removal',
                'status' => 0,
                'connector' => 'internal',
                'raw_parameters' => 'a:0:{}',
                'type' => 'notify_on_attribute_removal',
            ]
        );
    }

    public function down(Schema $schema): void
    {
        $this->throwIrreversibleMigrationException();
    }
}
