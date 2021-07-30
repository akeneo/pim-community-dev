<?php declare(strict_types=1);

namespace Pim\Upgrade\Schema;

use Akeneo\ReferenceEntity\Domain\Model\ReferenceEntity\ReferenceEntityIdentifier;
use Akeneo\Tool\Bundle\ElasticsearchBundle\Client;
use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;
use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

final class Version_6_0_20210730113235_update_ref_entities_mapping extends AbstractMigration implements ContainerAwareInterface
{
    private ContainerInterface $container;

    public function up(Schema $schema) : void
    {
        $this->disableMigrationWarning();
        /** @var Client $client */
        $client = $this->container->get('akeneo_referenceentity.client.record');
        $client->resetIndex();

        $this->reindexAllRecords();
    }

    public function down(Schema $schema) : void
    {
        $this->throwIrreversibleMigrationException();
    }

    public function setContainer(ContainerInterface $container = null)
    {
        $this->container = $container;
    }

    protected function reindexAllRecords(): void
    {
        $recordIndexer = $this->container->get('akeneo_referenceentity.infrastructure.search.elasticsearch.record_indexer');

        $allReferenceEntities = $this->getAllReferenceEntities();;
        foreach ($allReferenceEntities as $referenceEntityIdentifier) {
            $recordIndexer->indexByReferenceEntity(ReferenceEntityIdentifier::fromString($referenceEntityIdentifier));
        }
    }

    private function getAllReferenceEntities(): \Iterator
    {
        $selectAllQuery = 'SELECT identifier FROM akeneo_reference_entity_reference_entity';
        $statement = $this->connection->executeQuery($selectAllQuery);
        $results = $statement->fetchAll();
        $statement->closeCursor();

        foreach ($results as $result) {
            yield $result['identifier'];
        }
    }

    private function disableMigrationWarning()
    {
        $this->addSql('SELECT * FROM oro_user LIMIT 1');
    }
}
