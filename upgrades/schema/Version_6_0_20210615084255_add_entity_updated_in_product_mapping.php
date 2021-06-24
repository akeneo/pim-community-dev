<?php declare(strict_types=1);

namespace Pim\Upgrade\Schema;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;
use Elasticsearch\ClientBuilder;
use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Webmozart\Assert\Assert;

/**
 * The goal is to add the new "entity_updated" field in product mapping.
 * We can't change the mapping for an existing field without reindexing all, but we can add a nonexistent field.
 * See https://www.elastic.co/guide/en/elasticsearch/reference/7.x/indices-put-mapping.html#indices-put-mapping
 */
final class Version_6_0_20210615084255_add_entity_updated_in_product_mapping extends AbstractMigration implements ContainerAwareInterface
{
    private ContainerInterface $container;

    public function up(Schema $schema) : void
    {
        $this->disableMigrationWarning();
        $indexHosts = $this->container->getParameter('index_hosts');
        $productAndProductModelIndexName = $this->container->getParameter('product_and_product_model_index_name');

        $builder = new ClientBuilder();
        $hosts = [$indexHosts];

        $client = $builder->setHosts($hosts)->build()->indices();

        $existingMapping = $client->getMapping(['index' => $productAndProductModelIndexName]);
        if (!\is_array($existingMapping) || !isset(current($existingMapping)['mappings']['properties'])) {
            throw new \RuntimeException('Unable to retrieve existing mapping.');
        }

        $client->putMapping([
            'index' => $productAndProductModelIndexName,
            'body' => [
                'properties' => [
                    'entity_updated' => ['type' => 'date'],
                ],
            ],
        ]);
    }

    public function down(Schema $schema) : void
    {
        $this->throwIrreversibleMigrationException();
    }

    public function setContainer(ContainerInterface $container = null)
    {
        Assert::notNull($container);
        $this->container = $container;
    }

    /**
     * Function that does a non altering operation on the DB using SQL to hide the doctrine warning stating that no
     * sql query has been made to the db during the migration process.
     */
    private function disableMigrationWarning(): void
    {
        $this->addSql('SELECT 1');
    }
}
