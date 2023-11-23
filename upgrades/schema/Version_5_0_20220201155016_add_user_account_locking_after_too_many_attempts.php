<?php declare(strict_types=1);

namespace Pim\Upgrade\Schema;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version_5_0_20220201155016_add_user_account_locking_after_too_many_attempts extends AbstractMigration
{
    public function up(Schema $schema) : void
    {
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');
        if ($this->hasColumn($schema, 'consecutive_authentication_failure_counter') &&
            $this->hasColumn($schema, 'authentication_failure_reset_date')) {
            $this->write('consecutive_authentication_failure_counter & authentication_failure_reset_date column already exists in oro_user');

            return;
        }

        $this->addSql('alter table oro_user add consecutive_authentication_failure_counter int default 0');
        $this->addSql('alter table oro_user add authentication_failure_reset_date datetime  default null');
    }

    public function down(Schema $schema) : void
    {
        $this->throwIrreversibleMigrationException();
    }

    private function hasColumn(Schema $schema, string $columnName)
    {
        return $schema->getTable('oro_user')->hasColumn($columnName);
    }
}
