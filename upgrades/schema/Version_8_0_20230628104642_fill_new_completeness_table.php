<?php

declare(strict_types=1);


namespace Pim\Upgrade\Schema;

use Akeneo\Pim\Enrichment\Bundle\Command\ZddMigrations\V20230512143522FillNewCompletenessTableZddMigration;
use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;
use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * @copyright 2023 Akeneo SAS (https://www.akeneo.com)
 * @license   https://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
final class Version_8_0_20230628104642_fill_new_completeness_table extends AbstractMigration  implements ContainerAwareInterface
{
    private ?ContainerInterface $container = null;

    public function getDescription(): string
    {
        return 'Fills the pim_catalog_completeness table based on the data of the pim_catalog_completeness table';
    }

    /**
     * {@inheritdoc}
     */
    public function up(Schema $schema): void
    {
        $this->skipIf(
            false === $schema->hasTable('pim_catalog_product_completeness'),
            <<<EOL
            The pim_catalog_product completeness table has not been created yet, please run 
            the Pim\Upgrade\Schema\Version_8_0_20230509160000_new_table_completeness before this one 
            EOL
        );

        $migrationAlreadyRun = (bool) $this->connection->fetchOne(
            <<<SQL
            SELECT EXISTS(
                SELECT * FROM pim_catalog_product_completeness
            );
            SQL
        );

        if ($migrationAlreadyRun) {
            // disable migration warnings
            $this->addSql('SELECT 1');

            return;
        }

        $this->getMigration()->migrateNotZdd();
    }

    /**
     * {@inheritdoc}
     */
    public function down(Schema $schema): void
    {
        $this->throwIrreversibleMigrationException();
    }

    /**
     * {@inheritdoc}
     */
    public function setContainer(ContainerInterface $container = null): void
    {
        $this->container = $container;
    }

    private function getMigration(): V20230512143522FillNewCompletenessTableZddMigration
    {
        return $this->container->get(V20230512143522FillNewCompletenessTableZddMigration::class);
    }
}
