<?php

declare(strict_types=1);

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2021 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace AkeneoTestEnterprise\Pim\Automation\Integration\RuleEngine\Query\Sql;

use Akeneo\Pim\Automation\RuleEngine\Bundle\Query\Sql\SqlGetAttributesRulesNumber;
use Akeneo\Pim\Structure\Component\AttributeTypes;
use Akeneo\Pim\Structure\Component\Model\Attribute;
use Akeneo\Test\Integration\Configuration;
use Akeneo\Test\Integration\TestCase;

final class SqlGetFamilyAttributesRulesNumberIntegration extends TestCase
{
    public function test_it_returns_the_number_of_rules_by_attribute_codes(): void
    {
        $attribute1 = $this->createAttribute('att1');
        $attribute2 = $this->createAttribute('att2');
        $attribute3 = $this->createAttribute('att3');

        $attributeId1 = $attribute1->getId();
        $attributeId2 = $attribute2->getId();

        $this->get('database_connection')->executeQuery('ALTER TABLE akeneo_rule_engine_rule_definition AUTO_INCREMENT = 1');

        $sql = <<<SQL
INSERT INTO akeneo_rule_engine_rule_definition (code, type, content, priority, impacted_subject_count) VALUES
    ('rule1', 'product', '{"actions": [{"type": "set", "field": "camera_brand", "value": "canon_brand"}], "conditions": [{"field": "family", "value": ["camcorders"], "operator": "IN"}, {"field": "name", "value": "Canon", "operator": "CONTAINS"}, {"field": "camera_brand", "value": ["canon_brand"], "operator": "NOT IN"}]}', 0, null),
    ('rule2', 'product', '{"actions": [{"type": "copy", "to_field": "camera_model_name", "from_field": "name"}], "conditions": [{"field": "family", "value": ["camcorders"], "operator": "IN"}, {"field": "camera_model_name", "operator": "EMPTY"}]}', 0, null),
    ('rule3', 'product', '{"actions": [{"type": "copy", "to_field": "camera_model_name", "from_field": "name"}], "conditions": [{"field": "family", "value": ["camcorders"], "operator": "IN"}, {"field": "camera_model_name", "operator": "EMPTY"}]}', 0, null)
SQL;
        $this->get('database_connection')->executeQuery($sql);

        $sql = <<<SQL
INSERT INTO akeneo_rule_engine_rule_relation (rule_id, resource_name, resource_id) VALUES
    (1, 'Akeneo\\\Pim\\\Structure\\\Component\\\Model\\\Attribute', $attributeId1),
    (2, 'Akeneo\\\Pim\\\Structure\\\Component\\\Model\\\Attribute', $attributeId2),
    (3, 'Akeneo\\\Pim\\\Structure\\\Component\\\Model\\\Attribute', $attributeId2)
SQL;
        $this->get('database_connection')->executeQuery($sql);

        $result = $this->get(SqlGetAttributesRulesNumber::class)->execute([
            $attribute1->getCode(),
            $attribute2->getCode(),
            $attribute3->getCode(),
        ]);

        $this->assertEquals(['att1' => 1, 'att2' => 2], $result);
    }

    private function createAttribute(string $code): Attribute
    {
        $attribute = $this->get('akeneo_integration_tests.base.attribute.builder')->build([
            'code' => $code,
            'type' => AttributeTypes::TEXT,
            'unique' => false,
            'group' => 'other',
            'localizable' => false
        ], true);

        $this->get('pim_catalog.saver.attribute')->save($attribute);

        return $attribute;
    }

    /**
     * {@inheritdoc}
     */
    protected function getConfiguration(): Configuration
    {
        return $this->catalog->useMinimalCatalog();
    }
}
