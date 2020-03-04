<?php declare(strict_types=1);

namespace Pim\Upgrade\Schema;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;
use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

final class Version_5_0_20200304140000_remove_nested_set_columns_product_model extends AbstractMigration
{
    public function up(Schema $schema) : void
    {
        $this->addSql('ALTER TABLE pim_catalog_product_model DROP lvl');
        $this->addSql('ALTER TABLE pim_catalog_product_model DROP lft');
        $this->addSql('ALTER TABLE pim_catalog_product_model DROP root');
        $this->addSql('ALTER TABLE pim_catalog_product_model DROP rgt');
    }

    public function down(Schema $schema) : void
    {
        $this->throwIrreversibleMigrationException();
    }
}
