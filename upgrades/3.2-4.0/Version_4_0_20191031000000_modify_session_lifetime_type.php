<?php declare(strict_types=1);

namespace Pim\Upgrade\Schema;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * In symfony 4.4, there is a BC break: the type of the lifetime session is an UNSIGNED INT now,
 * @see https://github.com/symfony/symfony/issues/34491
 */
final class Version_4_0_20191031000000_modify_session_lifetime_type extends AbstractMigration
{
    public function up(Schema $schema) : void
    {
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE pim_session MODIFY sess_lifetime INTEGER UNSIGNED NOT NULL');
    }

    public function down(Schema $schema) : void
    {
        $this->throwIrreversibleMigrationException();
    }
}
