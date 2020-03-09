<?php declare(strict_types=1);

namespace Pim\Upgrade\Schema;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;
use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

final class Version_5_0_20200309165300_remove_product_foreign_key_pim_catalog_completeness extends AbstractMigration
{
    public function up(Schema $schema) : void
    {
        $this->addSql('ALTER TABLE pim_catalog_completeness DROP FOREIGN KEY pim_catalog_completeness_ibfk_1');
    }

    public function down(Schema $schema) : void
    {
        $this->addSql('ALTER TABLE pim_catalog_completeness ADD FOREIGN KEY pim_catalog_completeness_ibfk_1 (product_id) REFERENCES pim_catalog_product (id)');
    }
}
