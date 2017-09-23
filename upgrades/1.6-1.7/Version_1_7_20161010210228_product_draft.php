<?php

namespace Pim\Upgrade\Schema;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;
use Pim\Component\Catalog\AttributeTypes;
use Pim\Upgrade\SchemaHelperEE;
use Pim\Upgrade\UpgradeHelper;
use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * As standard format has been changed, we have to update the table `pimee_workflow_product_draft` to update keys of
 * `metric` and `price` attributes (`data` has been changed to `amount`).
 */
class Version_1_7_20161010210228_product_draft extends AbstractMigration implements ContainerAwareInterface
{
    /** @var ContainerInterface */
    protected $container;

    /**
     * {@inheritdoc}
     */
    public function setContainer(ContainerInterface $container = null)
    {
        $this->container = $container;
    }

    /**
     * @param Schema $schema
     */
    public function up(Schema $schema)
    {
        $upgradeHelper = new UpgradeHelper($this->container);
        $schemaHelper = new SchemaHelperEE($this->container);

        $attributes = $this->container->get('pim_catalog.repository.attribute')->findBy([
            'type' => [AttributeTypes::METRIC, AttributeTypes::PRICE_COLLECTION]
        ]);

        $attributeCodes = ['metric' => [], 'price' => []];
        foreach ($attributes as $attribute) {
            if (AttributeTypes::METRIC === $attribute->getAttributeType()) {
                $attributeCodes['metric'][] = $attribute->getCode();
            }

            if (AttributeTypes::PRICE_COLLECTION === $attribute->getAttributeType()) {
                $attributeCodes['price'][] = $attribute->getCode();
            }
        }

        $drafts = $this->getdrafts($upgradeHelper, $schemaHelper);

        foreach ($drafts as $draft) {
            if (isset($draft['changes'])) {
                $values = $upgradeHelper->areProductsStoredInMongo() ?
                    $draft['changes'] : json_decode($draft['changes'], true);

                foreach ($values['values'] as $code => $value) {
                    foreach ($value as $index => $data) {
                        if (in_array($code, $attributeCodes['metric']) && isset($data['data']['data'])) {
                            $data['data']['amount'] = $data['data']['data'];
                            unset($data['data']['data']);
                        } elseif (in_array($code, $attributeCodes['price'])) {
                            foreach ($data['data'] as $indexPrice => $price) {
                                if (isset($price['data'])) {
                                    $data['data'][$indexPrice]['amount'] = $price['data'];
                                    unset($data['data'][$indexPrice]['data']);
                                }
                            }
                        }

                        $values['values'][$code][$index] = $data;
                    }
                }

                $this->save($upgradeHelper, $schemaHelper, $values, $draft);
            }
        }
    }

    /**
     * @param UpgradeHelper  $upgradeHelper
     * @param SchemaHelperEE $schemaHelper
     *
     * @return array|\MongoCursor
     */
    protected function getdrafts(UpgradeHelper $upgradeHelper, SchemaHelperEE $schemaHelper)
    {
        if (!$upgradeHelper->areProductsStoredInMongo()) {
            $sql = 'SELECT * FROM ' . $schemaHelper->getTableOrCollection('product_draft');
            $drafts = $this->connection->fetchAll($sql);
        } else {
            $collection = $schemaHelper->getTableOrCollection('product_draft');
            $draftCollection = new \MongoCollection($upgradeHelper->getMongoInstance(), $collection);
            $drafts = $draftCollection->find();
        }

        return $drafts;
    }

    /**
     * @param UpgradeHelper  $upgradeHelper
     * @param SchemaHelperEE $schemaHelper
     * @param array          $values
     * @param array          $draft
     */
    protected function save(UpgradeHelper $upgradeHelper, SchemaHelperEE $schemaHelper, array $values, array $draft)
    {
        $collection = $schemaHelper->getTableOrCollection('product_draft');

        if (!$upgradeHelper->areProductsStoredInMongo()) {
            $this->connection->update(
                $collection,
                ['changes' => json_encode($values)],
                ['id'      => $draft['id']]
            );
        } else {
            $draftCollection = new \MongoCollection($upgradeHelper->getMongoInstance(), $collection);

            $draftCollection->update(
                ['_id' => $draft['_id']],
                [
                    '$set' => ['changes' => $values]
                ]
            );
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
