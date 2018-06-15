<?php

namespace Pim\Upgrade\Schema;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;
use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Symfony\Component\DependencyInjection\ContainerAwareTrait;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
class Version20180615152443 extends AbstractMigration implements ContainerAwareInterface
{
    use ContainerAwareTrait;

    const BATCH_SIZE = 1000;

    /** @var int */
    protected $fromLimit = 0;

    /** @var int */
    protected $toLimit = 0;

    /**
     * @param Schema $schema
     */
    public function up(Schema $schema)
    {
        $this->abortIf($this->connection->getDatabasePlatform()->getName() != 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('
        CREATE TABLE `pimee_workflow_product_model_draft` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `product_model_id` int(11) NOT NULL,
  `created_at` datetime NOT NULL COMMENT \'(DC2Type:datetime)\',
  `changes` longtext COLLATE utf8_unicode_ci NOT NULL COMMENT \'(DC2Type:json_array)\',
  `raw_values` json NOT NULL COMMENT \'(DC2Type:native_json)\',
  `author` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `status` int(11) NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `unique_idx` (`author`,`product_model_id`),
  KEY `IDX_68577CF4B2C5DD70` (`product_model_id`),
  CONSTRAINT `FK_68577CF4B2C5DD70` FOREIGN KEY (`product_model_id`) REFERENCES `pim_catalog_product_model` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
');

        $productDraftRepository = $this->container->get('pimee_workflow.repository.product_draft');
        $productProposalIndexer = $this->container->get('pim_catalog.elasticsearch.product_proposal_indexer');
        $client = $this->container->get('akeneo_elasticsearch.client.product_proposal');
        $client->resetIndex();

        while (null !== $draftsIds = $this->getNextProductDrafts()) {
            $this->connection->beginTransaction();

            $drafts = $productDraftRepository->findByIds($draftsIds);
            $productProposalIndexer->indexAll($drafts);

            $clearer = $this->container->get('pim_connector.doctrine.cache_clearer');
            $clearer->clear();

            $this->connection->commit();
        }

//        $this->disableMigrationWarning();
    }

    /**
     * @param Schema $schema
     */
    public function down(Schema $schema)
    {
        $this->throwIrreversibleMigrationException();
    }

    /**
     * Returns next ProductDraft changes or null if it does not remain.
     *
     * @return array|null
     */
    private function getNextProductDrafts()
    {
        $this->fromLimit = $this->toLimit;
        $this->toLimit += self::BATCH_SIZE;

        $selectDraftPattern =
            'SELECT draft.id as id FROM pimee_workflow_product_draft as draft LIMIT %s,%s';

        $draftsStmt = $this->connection->query(sprintf($selectDraftPattern, $this->fromLimit, $this->toLimit));
        $draftsStmt->execute();

        return 0 !== $draftsStmt->rowCount() ? $draftsStmt->fetchAll() : null;
    }

    /**
     * Function that does a non altering operation on the DB using SQL to hide the doctrine warning stating that no
     * sql query has been made to the db during the migration process.
     */
    private function disableMigrationWarning()
    {
        $this->addSql('SELECT * FROM oro_user LIMIT 1');
    }
}
