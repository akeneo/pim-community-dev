<?php

namespace PimEnterprise\Component\CatalogRule\tests\integration\Validator;

use Akeneo\Test\Integration\TestCase;
use Pim\Component\Catalog\AttributeTypes;
use Pim\Component\Connector\Exception\InvalidItemFromViolationsException;

class PropertyActionValidatorIntegration extends TestCase
{
    protected function setUp()
    {
        parent::setUp();

        $this->createAttribute([
            'code' => 'another_text',
            'type' => AttributeTypes::TEXT,
            'localizable' => false,
            'scopable' => false,
        ]);
    }

    /**
     * @see https://akeneo.atlassian.net/browse/PIM-6930
     */
    public function testValidationOnARuleRegexConstraintOnSku()
    {
        $skuAttribute = $this->getFromTestContainer('pim_api.repository.attribute')->findOneByIdentifier('sku');
        $skuAttribute->setValidationRule('regexp');
        $skuAttribute->setValidationRegexp('/^foo$/');
        $this->getFromTestContainer('pim_catalog.saver.attribute')->save($skuAttribute);

        $item  = [
            'actions' => [
                [
                    'from_field' => 'a_text',
                    'from_locale' => null,
                    'to_field' => 'another_text',
                    'to_locale' => null,
                    'type' => 'copy'
                ]
            ],
            'rule_name' => null,
            'code' => 'rule_code',
            'conditions' => [],
            'priority' => 100,
        ];


        $processor = $this->get('pimee_catalog_rule.processor.denormalization.rule_definition');

        $exception = null;
        try {
            $processor->process($item);
        } catch (InvalidItemFromViolationsException $exception) {}

        $this->assertNull($exception, 'Unexpected InvalidItemFromViolationsException');
    }

    protected function getConfiguration()
    {
        return $this->catalog->useTechnicalCatalog();
    }

    /**
     * @param array $data
     */
    private function createAttribute(array $data)
    {
        $data['group'] = $data['group'] ?? 'other';

        $attribute = $this->getFromTestContainer('pim_catalog.factory.attribute')->create();
        $this->getFromTestContainer('pim_catalog.updater.attribute')->update($attribute, $data);
        $constraints = $this->getFromTestContainer('validator')->validate($attribute);
        $this->assertCount(0, $constraints);
        $this->getFromTestContainer('pim_catalog.saver.attribute')->save($attribute);
    }
}
