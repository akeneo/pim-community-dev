<?php

declare(strict_types=1);

namespace Pim\Upgrade\Schema;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;
use Elasticsearch\ClientBuilder;
use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Webmozart\Assert\Assert;

/**
 * This migration adds a mapped field (id) to connection_error index mapping
 */
final class Version_7_0_20221027152057_add_id_field_to_connection_error_index_mapping extends AbstractMigration implements ContainerAwareInterface
{
    private ContainerInterface $container;

    public function up(Schema $schema) : void
    {
        $this->disableMigrationWarning();
        $indexHosts = $this->container->getParameter('index_hosts');
        $connectionErrorIndexName = $this->container->getParameter('connection_error_index_name');

        $builder = new ClientBuilder();

        $client = $builder->setHosts([$indexHosts])->build()->indices();

        $existingMapping = $client->getMapping(['index' => $connectionErrorIndexName]);
        if (!\is_array($existingMapping) || !isset(current($existingMapping)['mappings']['properties'])) {
            throw new \RuntimeException('Unable to retrieve existing mapping.');
        }

        $client->putMapping([
            'index' => $connectionErrorIndexName,
            'body' => [
                'properties' => [
                    'id' => ['type' => 'keyword'],
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

    private function disableMigrationWarning(): void
    {
        $this->addSql('SELECT 1');
    }

}
