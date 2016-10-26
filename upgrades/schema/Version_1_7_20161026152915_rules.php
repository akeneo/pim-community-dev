<?php

namespace Pim\Upgrade\Schema;

use Doctrine\DBAL\DBALException;
use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;
use Pim\Component\Catalog\AttributeTypes;

/**
 * Change "akeneo_rule_engine_rule_definition" to change key "data" to "amount" for price & metric
 */
class Version_1_7_20161026152915_rules extends AbstractMigration
{
    const RULE_DEFINITION_TABLE = 'akeneo_rule_engine_rule_definition';
    const ATTRIBUTE_TABLE = 'pim_catalog_attribute';

    /**
     * @param Schema $schema
     */
    public function up(Schema $schema)
    {
        $priceAndMetricAttributes = $this->getMetricAndPriceAttributes();
        $mediaAttributes = $this->getMediaAttributes();
        $stmt = $this->connection->prepare(sprintf('SELECT id, content FROM %s', self::RULE_DEFINITION_TABLE));

        $stmt->execute();
        $rules = $stmt->fetchAll();

        foreach ($rules as $rule) {
            $content = json_decode($rule['content'], true);

            $content = $this->changeData('conditions', $content, $priceAndMetricAttributes, $mediaAttributes);
            $content = $this->changeData('actions', $content, $priceAndMetricAttributes, $mediaAttributes);

            $this->connection->update(
                self::RULE_DEFINITION_TABLE,
                ['content' => json_encode($content)],
                ['id'      => $rule['id']]
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

    /**
     * @throws DBALException
     *
     * @return array
     */
    protected function getMetricAndPriceAttributes()
    {
        $stmt = $this->connection->prepare(sprintf('SELECT code FROM %s WHERE attribute_type IN ("%s", "%s")',
            self::ATTRIBUTE_TABLE, AttributeTypes::PRICE_COLLECTION, AttributeTypes::METRIC
        ));

        $stmt->execute();

        $attributes = [];
        foreach ($stmt->fetchAll() as $attribute) {
            $attributes[] = $attribute['code'];
        }

        return $attributes;
    }

    /**
     * @return array
     *
     * @throws DBALException
     */
    protected function getMediaAttributes()
    {
        $stmt = $this->connection->prepare(sprintf('SELECT code FROM %s WHERE attribute_type IN ("%s", "%s")',
            self::ATTRIBUTE_TABLE, AttributeTypes::IMAGE, AttributeTypes::FILE
        ));

        $stmt->execute();

        $attributes = [];
        foreach ($stmt->fetchAll() as $attribute) {
            $attributes[] = $attribute['code'];
        }

        return $attributes;
    }

    /**
     * @param string $typeName
     * @param array  $content
     * @param array  $priceAndMetricAttributes
     * @param array  $mediaAttributes
     *
     * @return array
     */
    protected function changeData($typeName, array $content, array $priceAndMetricAttributes, array $mediaAttributes)
    {
        if (isset($content[$typeName])) {
            foreach ($content[$typeName] as $index => $type) {
                // if current field is a price or a metric, change key "data" to "amount"
                if (isset($type['field']) && in_array($type['field'], $priceAndMetricAttributes)) {
                    if (isset($type['value']['data'])) {
                        $type['value']['amount'] = $type['value']['data'];
                        unset($type['value']['data']);
                    } else {
                        foreach ($type['value'] as $i => $value) {
                            if (isset($value['data'])) {
                                $type['value'][$i]['amount'] = $value['data'];
                                unset($type['value'][$i]['data']);
                            }
                        }
                    }

                    $content[$typeName][$index] = $type;
                }

                // if current field is a file, remove keys "filePath" and "originalFilename" to keep only the value of "filePath"
                if (isset($type['field']) && isset($type['value']['filePath']) && in_array($type['field'], $mediaAttributes)) {
                    $content[$typeName][$index]['value'] = $type['value']['filePath'];
                }
            }
        }

        return $content;
    }
}
