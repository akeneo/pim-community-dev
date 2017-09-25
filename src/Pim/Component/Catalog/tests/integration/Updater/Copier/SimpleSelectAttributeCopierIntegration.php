<?php

namespace Pim\Component\Catalog\tests\integration\Updater\Copier;

use Pim\Component\Catalog\AttributeTypes;

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

        $this->get('pim_catalog.updater.product_property_copier')->copyData(
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
            $newValue->getAttribute()->getCode()
        );
        $this->assertSame(
            '[optionA]',
            (string)$newValue
        );
        $this->assertSame(
            $this->get('pim_catalog.repository.attribute_option')->findOneByIdentifier('another_simple_select.optionA'),
            $newValue->getData()
        );
    }

    /**
     * {@inheritdoc}
     */
    protected function setUp()
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
