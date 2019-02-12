<?php

namespace AkeneoTest\Pim\Enrichment\Integration\Updater\Copier;

use Akeneo\Pim\Structure\Component\AttributeTypes;

/**
 * @author    Damien Carcel (damien.carcel@akeneo.com)
 * @copyright 2017 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 */
class SimpleSelectAttributeCopierIntegration extends AbstractCopierTestCase
{
    public function testCopySimpleSelectAttributeValue()
    {
        $product = $this->createProduct('test-copy-simple-select', [
            'values' => [
                'a_simple_select' => [
                    [
                        'data' => 'optionA',
                        'locale' => null,
                        'scope' => null,
                    ],
                ],
            ],
        ]);

        $this->get('pim_catalog.updater.property_copier')->copyData(
            $product,
            $product,
            'a_simple_select',
            'another_simple_select'
        );

        $errors = $this->get('pim_catalog.validator.product')->validate($product);
        $this->assertEquals(0, $errors->count());

        $newValue = $product->getValue('another_simple_select');

        $this->assertSame(
            'another_simple_select',
            $newValue->getAttributeCode()
        );
        $this->assertSame(
            '[optionA]',
            (string)$newValue
        );
        $this->assertSame(
            $this->get('pim_catalog.repository.attribute_option')->findOneByIdentifier('another_simple_select.optionA')->getCode(),
            $newValue->getData()
        );
    }

    /**
     * {@inheritdoc}
     */
    protected function setUp(): void
    {
        parent::setUp();

        $optionA = $this->get('pim_catalog.factory.attribute_option')->create();
        $optionA->setCode('optionA');

        $simpleSelectAttribute = $this->get('pim_catalog.factory.attribute')->createAttribute(
            AttributeTypes::OPTION_SIMPLE_SELECT
        );
        $simpleSelectAttribute->setCode('another_simple_select');
        $simpleSelectAttribute->addOption($optionA);

        $this->get('pim_catalog.saver.attribute')->save($simpleSelectAttribute);
    }
}
