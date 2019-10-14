<?php declare(strict_types=1);

namespace Pim\Upgrade\Schema;

use Akeneo\Apps\Infrastructure\Install\Query\CreateAppsTableQuery;
use Akeneo\Pim\Enrichment\Bundle\Elasticsearch\Indexer\ProductModelDescendantsIndexer;
use Akeneo\Pim\Enrichment\Bundle\Elasticsearch\Indexer\ProductModelIndexer;
use Akeneo\Pim\Enrichment\Component\Product\Factory\EmptyValuesCleaner;
use Doctrine\DBAL\Schema\Schema;
use Doctrine\DBAL\Types\Type;
use Doctrine\Migrations\AbstractMigration;
use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * This migration will delete the empty values from raw values of product models.
 * For example, the value {attr: {<all_channels>: {<all_locales>: []}}} will be removed from the raw_values field.
 */
final class Version_4_0_20191014111427_create_apps_table
    extends AbstractMigration
    implements ContainerAwareInterface
{
    /** @var ContainerInterface */
    private $container;

    /**
     * {@inheritdoc}
     */
    public function setContainer(ContainerInterface $container = null)
    {
        $this->container = $container;
    }

    public function up(Schema $schema) : void
    {
        $dbalConnection = $this->container->get('database_connection');
        $dbalConnection->exec(CreateAppsTableQuery::QUERY);
    }

    public function down(Schema $schema) : void
    {
        $dropTableQuery = <<<SQL
DROP TABLE akeneo_app
SQL;

        $dbalConnection = $this->container->get('database_connection');
        $dbalConnection->exec($dropTableQuery);
    }
}
