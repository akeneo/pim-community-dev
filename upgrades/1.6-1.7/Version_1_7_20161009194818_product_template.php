<?php

namespace Pim\Upgrade\Schema;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;
use Pim\Component\Catalog\AttributeTypes;
use Pim\Upgrade\SchemaHelper;
use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * As standard format has been changed, we have to update the table `pim_catalog_product_template` to update keys of
 * `metric` and `price` attributes (`data` has been changed to `amount`). Also `image` attributes should be fixed.
 */
class Version_1_7_20161009194818_product_template extends AbstractMigration implements ContainerAwareInterface
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
        $schemaHelper = new SchemaHelper($this->container);

        $attributes = $this->container->get('pim_catalog.repository.attribute')->findBy([
            'type' => [AttributeTypes::METRIC, AttributeTypes::PRICE_COLLECTION, AttributeTypes::IMAGE]
        ]);

        $attributeCodes = ['metric' => [], 'price' => [], 'image' => []];
        foreach ($attributes as $attribute) {
            if (AttributeTypes::METRIC === $attribute->getType()) {
                $attributeCodes['metric'][] = $attribute->getCode();
            }

            if (AttributeTypes::PRICE_COLLECTION === $attribute->getType()) {
                $attributeCodes['price'][] = $attribute->getCode();
            }

            if (AttributeTypes::IMAGE === $attribute->getAttributeType()) {
                $attributeCodes['image'][] = $attribute->getCode();
            }
        }

        $table = $schemaHelper->getTableOrCollection('product_template');
        $templates = $this->connection->fetchAll('SELECT * FROM ' . $table);
        foreach ($templates as $template) {
            if (isset($template['valuesData'])) {
                $values = json_decode($template['valuesData'], true);

                foreach ($values as $code => $value) {
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
                        } elseif (in_array($code, $attributeCodes['image'])) {
                            $data['data'] = isset($data['data']['filePath']) ? $data['data']['filePath'] : null;
                        }

                        $values[$code][$index] = $data;
                    }
                }

                $this->connection->update(
                    $table,
                    ['valuesData' => json_encode($values)],
                    ['id'         => $template['id']]
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
