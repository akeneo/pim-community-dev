<?php

declare(strict_types=1);

namespace Pim\Upgrade\Schema;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version_7_0_20220824134638_add_missing_remove_completeness_for_channel_and_locale_job_instance extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Adds the potentially missing remove_completeness_for_channel_and_locale job instance';
    }

    public function up(Schema $schema): void
    {
        $this->addSql(
            <<<SQL
            INSERT INTO akeneo_batch_job_instance (code, label, job_name, status, connector, raw_parameters, type)
            VALUES (:code, :label, :job_name, :status, :connector, :raw_parameters, :type)
            ON DUPLICATE KEY UPDATE label = label;
            SQL,
            [
                'code' => 'remove_completeness_for_channel_and_locale',
                'label' => 'Remove completeness for channel and locale',
                'job_name' => 'remove_completeness_for_channel_and_locale',
                'status' => 0,
                'connector' => 'internal',
                'raw_parameters' => 'a:0:{}',
                'type' => 'remove_completeness_for_channel_and_locale',
            ]
        );
    }

    public function down(Schema $schema): void
    {
        $this->throwIrreversibleMigrationException();
    }
}
