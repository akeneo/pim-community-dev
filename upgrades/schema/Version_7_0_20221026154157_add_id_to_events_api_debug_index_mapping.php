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
 * This migration aims to add a new field (id) to the events_api_debug ES index mapping
 */
final class Version_7_0_20221026154157_add_id_to_events_api_debug_index_mapping extends AbstractMigration implements ContainerAwareInterface
{
    private ContainerInterface $container;

    public function up(Schema $schema): void
    {
        $this->disableMigrationWarning();
        $indexHosts = $this->container->getParameter('index_hosts');
        $eventsApiDebugIndexName = $this->container->getParameter('events_api_debug_index_name');

        $builder = new ClientBuilder();

        $client = $builder->setHosts([$indexHosts])->build()->indices();

        $existingMapping = $client->getMapping(['index' => $eventsApiDebugIndexName]);
        if (!\is_array($existingMapping) || !isset(current($existingMapping)['mappings']['properties'])) {
            throw new \RuntimeException('Unable to retrieve existing mapping.');
        }

        $client->putMapping([
            'index' => $eventsApiDebugIndexName,
            'body' => [
                'properties' => [
                    'id' => ['type' => 'keyword'],
                ],
            ],
        ]);

    }

    public function down(Schema $schema): void
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
