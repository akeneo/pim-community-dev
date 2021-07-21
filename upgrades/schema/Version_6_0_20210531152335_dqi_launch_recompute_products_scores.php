<?php declare(strict_types=1);

namespace Pim\Upgrade\Schema;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;
use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

final class Version_6_0_20210531152335_dqi_launch_recompute_products_scores extends AbstractMigration  implements ContainerAwareInterface
{
    private ?ContainerInterface $container;

    public function up(Schema $schema) : void
    {
        $this->removeMigrationWarning();

        $this->container->get('pim_catalog.command_launcher')->executeForeground(
            'pim:data-quality-insights:recompute-product-scores'
        );
    }

    public function setContainer(ContainerInterface $container = null)
    {
        if ($container !== null) {
            $this->container = $container;
        }
    }

    private function removeMigrationWarning(): void
    {
        $this->addSql('SELECT 1');
    }

    public function down(Schema $schema) : void
    {
        $this->throwIrreversibleMigrationException();
    }
}
