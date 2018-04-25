<?php

namespace Pim\Upgrade\Schema;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;
use PimEnterprise\Component\Workflow\Model\ProductDraft;
use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Symfony\Component\DependencyInjection\ContainerAwareTrait;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
class Version_2_2_180220105049 extends AbstractMigration implements ContainerAwareInterface
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
        $this->connection->beginTransaction();
        $this->connection->query('ALTER TABLE pimee_workflow_product_draft ADD raw_values json NOT NULL COMMENT \'(DC2Type:native_json)\'');
        $this->connection->commit();

        $client = $this->container->get('akeneo_elasticsearch.client.product_proposal');
        if (!$client->hasIndex()) {
            $client->createIndex();
        }

        while (null !== $draftsChanges = $this->getNextProductDrafts()) {
            $this->connection->beginTransaction();

            $this->updateProductDraftsRawValues($draftsChanges);

            $this->connection->commit();
        }

        $this->disableMigrationWarning();
    }

    /**
     * @param Schema $schema
     *
     * @throws \Doctrine\DBAL\Migrations\IrreversibleMigrationException
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
            'SELECT draft.id as id, draft.changes as changes, draft.status as status FROM pimee_workflow_product_draft as draft LIMIT %s,%s';

        $draftsStmt = $this->connection->query(sprintf($selectDraftPattern, $this->fromLimit, $this->toLimit));
        $draftsStmt->execute();

        return 0 !== $draftsStmt->rowCount() ? $draftsStmt->fetchAll() : null;
    }

    /**
     * @param array $drafts
     */
    private function updateProductDraftsRawValues(array $drafts)
    {
        $productValueFactory = $this->container->get('pim_catalog.factory.value');
        $attributeRepository = $this->container->get('pim_catalog.repository.cached_attribute');
        $productDraftRepository = $this->container->get('pimee_workflow.repository.product_draft');
        $productProposalIndexer = $this->container->get('pim_catalog.elasticsearch.product_proposal_indexer');

        $localeRepository = $this->container->get('pim_catalog.repository.locale');
        $activatedLocaleCodes = $localeRepository->getActivatedLocaleCodes();

        $scopeRepository = $this->container->get('pim_catalog.repository.channel');
        $scopeCodes = $scopeRepository->getChannelCodes();

        $updateSql = 'UPDATE pimee_workflow_product_draft as draft SET draft.raw_values = :rawValues WHERE draft.id = :id';

        foreach ($drafts as $draft) {
            $productValues = [];
            $changes = json_decode($draft['changes'], true);
            foreach ($changes['values'] as $attributeCode => $values) {
                foreach ($values as $value){
                    $attribute = $attributeRepository->findOneByIdentifier($attributeCode);

                    if (null !== $attribute) {
                        if (!$attribute->isScopable() && !$attribute->isLocalizable()) {
                            $productValues[] = $productValueFactory->create(
                                $attribute,
                                null,
                                null,
                                $value['data']
                            );
                        } elseif (($attribute->isScopable() && in_array($value['scope'], $scopeCodes)) && !$attribute->isLocalizable()) {
                            $productValues[] = $productValueFactory->create(
                                $attribute,
                                $value['scope'],
                                null,
                                $value['data']
                            );
                        } elseif (($attribute->isLocalizable() && in_array($value['locale'], $activatedLocaleCodes)) && !$attribute->isScopable()) {
                            $productValues[] = $productValueFactory->create(
                                $attribute,
                                null,
                                $value['locale'],
                                $value['data']
                            );
                        } elseif (in_array($value['scope'], $scopeCodes) && in_array($value['locale'], $activatedLocaleCodes)) {
                            $productValues[] = $productValueFactory->create(
                                $attribute,
                                $value['scope'],
                                $value['locale'],
                                $value['data']
                            );
                        }
                    }
                }
            }

            $rawValues = $this->container->get('pim_serializer')->normalize($productValues, 'storage');
            $rawValues = call_user_func_array('array_merge_recursive', $rawValues);
            $jsonValues = json_encode($rawValues);

            $updateStmt = $this->connection->prepare($updateSql);
            $updateStmt->bindValue('id', $draft['id']);
            $updateStmt->bindValue('rawValues', $jsonValues);
            $updateStmt->execute();
        }

        $drafts = array_filter(
            $drafts,
            function ($draft) {
                return ProductDraft::READY == $draft['status'];
            }
        );

        $draftsIds = array_map(
            function ($draft) {
                return $draft['id'];
            },
            $drafts
        );

        $drafts = $productDraftRepository->findByIds($draftsIds);
        $productProposalIndexer->indexAll($drafts);

        $clearer = $this->container->get('pim_connector.doctrine.cache_clearer');
        $clearer->clear();
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
