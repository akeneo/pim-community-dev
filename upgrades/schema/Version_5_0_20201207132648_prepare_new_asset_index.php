<?php declare(strict_types=1);

namespace Pim\Upgrade\Schema;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * @copyright 2021 Akeneo SAS (http://www.akeneo.com)
 *
 * This migration was a one-shot. We re-indexed on a temporary index (with a new mapping)
 * and another migration will switch the alias to this temporary index.
 * Now there is no reason to execute it, and the services do not exist anymore.
 * As the migrations must not be deleted we keep this file, event if today the migration does nothing.
 */
final class Version_5_0_20201207132648_prepare_new_asset_index extends AbstractMigration
{
    private ContainerInterface $container;

    public function up(Schema $schema) : void
    {
        $this->disableMigrationWarning();
    }

    public function down(Schema $schema) : void
    {
        $this->throwIrreversibleMigrationException();
    }

    /**
     * Function that does a non altering operation on the DB using SQL to hide the doctrine warning stating that no
     * sql query has been made to the db during the migration process.
     */
    private function disableMigrationWarning()
    {
        $this->addSql('SELECT 1');
    }
}
