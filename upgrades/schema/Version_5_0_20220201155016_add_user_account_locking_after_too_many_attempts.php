<?php declare(strict_types=1);

namespace Pim\Upgrade\Schema;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version_5_0_20220201155016_add_user_account_locking_after_too_many_attempts extends AbstractMigration
{
    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');
        $this->skipIf(
            $this->hasColumn('consecutive_authentication_failure_counter') && $this->hasColumn('authentication_failure_reset_date'),
            'consecutive_authentication_failure_counter & authentication_failure_reset_date column already exists in oro_user'
        );

        $this->addSql('alter table oro_user add consecutive_authentication_failure_counter int default 0');
        $this->addSql('alter table oro_user add authentication_failure_reset_date datetime  default null ');
    }

    public function down(Schema $schema) : void
    {}

    private function hasColumn(string $columnName)
    {
        return $schema->getTable('oro_user')->hasColumn($columnName);
    }
}
