<?php

namespace Pim\Upgrade\Schema;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * This migration triggers the reindexation of the products and product models.
 */
class Version_2_3_20180515121925_index_products_and_product_models extends AbstractMigration
{
    /**
     * @param Schema $schema
     */
    public function up(Schema $schema)
    {
        $this->disableMigrationWarning();
    }

    public function postUp(Schema $schema)
    {
        $kernel = new \AppKernel(getenv('SYMFONY_ENV') ?: 'prod', (bool) getenv('SYMFONY_DEBUG'));
        $application = new Application($kernel);
        $application->setAutoExit(false);
        $application->run(new ArrayInput([
            'command' => 'pim:product-model:index',
            '--all'   => true,
        ]));
        $application->run(new ArrayInput([
            'command' => 'pim:product:index',
            '--all'   => true,
        ]));
    }

    /**
     * @param Schema $schema
     */
    public function down(Schema $schema)
    {
        $this->throwIrreversibleMigrationException();
    }

    /**
     * Function that does a non altering operation on the DB using SQL to hide the doctrine warning stating that no
     * sql query has been made to the db during the migration process.
     */
    private function disableMigrationWarning()
    {
        $this->addSql('SELECT * FROM oro_user LIMIT 1');
    }
}
