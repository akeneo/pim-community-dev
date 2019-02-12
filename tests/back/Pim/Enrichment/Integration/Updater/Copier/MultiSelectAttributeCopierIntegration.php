<?php

namespace AkeneoTest\Pim\Enrichment\Integration\Updater\Copier;

use Akeneo\Pim\Structure\Component\AttributeTypes;

/**
 * @author    Damien Carcel (damien.carcel@akeneo.com)
 * @copyright 2017 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 */
class MultiSelectAttributeCopierIntegration extends AbstractCopierTestCase
{
    public function testCopyMultiSelectAttributeValue()
    {
        $product = $this->createProduct('test-copy-multi-select', [
            'values' => [
                'a_multi_select' => [
                    [
                        'data' => ['optionA', 'optionB'],
                        'locale' => null,
                        'scope' => null,
                    ],
                ],
            ],
        ]);

        $this->get('pim_catalog.updater.property_copier')->copyData(
            $product,
            $product,
            'a_multi_select',
            'another_multi_select'
        );

        $errors = $this->get('pim_catalog.validator.product')->validate($product);
        $this->assertEquals(0, $errors->count());

        $newValue = $product->getValue('another_multi_select');

        $this->assertSame(
            'another_multi_select',
            $newValue->getAttributeCode()
        );
        $this->assertSame(
            '[optionA], [optionB]',
            (string)$newValue
        );

        foreach ($newValue->getData() as $actualOptionCode) {
            $expectedOptionCode = $this
                ->get('pim_catalog.repository.attribute_option')
                ->findOneByIdentifier(sprintf('another_multi_select.%s', $actualOptionCode))->getCode();

            $this->assertSame($expectedOptionCode, $actualOptionCode);
        }
    }

    /**
     * {@inheritdoc}
     */
    protected function setUp(): void
    {
        parent::setUp();

        $optionA = $this->get('pim_catalog.factory.attribute_option')->create();
        $optionA->setCode('optionA');

        $optionB = $this->get('pim_catalog.factory.attribute_option')->create();
        $optionB->setCode('optionB');

        $simpleSelectAttribute = $this->get('pim_catalog.factory.attribute')->createAttribute(
            AttributeTypes::OPTION_MULTI_SELECT
        );
        $simpleSelectAttribute->setCode('another_multi_select');
        $simpleSelectAttribute->addOption($optionA);
        $simpleSelectAttribute->addOption($optionB);

        $this->get('pim_catalog.saver.attribute')->save($simpleSelectAttribute);
    }
}
