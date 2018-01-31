<?php

namespace Pim\Upgrade\Schema;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;
use Pim\Upgrade\SchemaHelper;
use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Symfony\Component\DependencyInjection\ContainerAwareTrait;

/**
 * Put id in null label for `pim_api_client` table
 */
class Version_2_0_20170823082403_udapte_client_label extends AbstractMigration implements ContainerAwareInterface
{
    use ContainerAwareTrait;
    /**
     * @param Schema $schema
     */
    public function up(Schema $schema)
    {
        $schemaHelper = new SchemaHelper($this->container);
        $clientTable = $schemaHelper->getTableOrCollection('client');

        $stmt = $this->connection->prepare(
            sprintf('SELECT id, label FROM %s WHERE label IS NULL', $clientTable)
        );

        $stmt->execute();
        $clients = $stmt->fetchAll();

        foreach ($clients as $client) {
            if (null === $client['label']) {
                $this->connection->update(
                    $clientTable,
                    ['label' => $client['id']],
                    ['id' => $client['id']]
                );
            }
        }
    }

    /**
     * @param Schema $schema
     */
    public function down(Schema $schema)
    {
        $this->throwIrreversibleMigrationException();
    }
}
