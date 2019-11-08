<?php

namespace AkeneoTest\Pim\Enrichment\Integration\Product;

use Akeneo\Pim\Enrichment\Component\Product\Model\ProductInterface;
use Akeneo\Pim\Enrichment\Component\Product\Model\ValueInterface;
use Akeneo\Pim\Structure\Component\Query\PublicApi\AttributeType\Attribute;
use Akeneo\Test\Integration\TestCase;

/**
 * Integration tests to verify a product is well loaded from database with its product values.
 */
class LoadEntityWithValuesSubscriberIntegration extends TestCase
{
    public function testLoadValuesForProductWithAllAttributes()
    {
        $product = $this->findProductByIdentifier('foo');
        $expectedValues = $this->getValuesFromStandardValues(
            $this->getStandardValuesWithAllAttributes()
        );

        $this->assertProductHasValues($expectedValues, $product);
    }

    public function testLoadValuesForVariantProductWithFewAttributes()
    {
        $product = $this->findProductByIdentifier('qux');
        $expectedValues = $this->getValuesFromStandardValues(
            $this->getStandardValuesOfVariantProduct()
        );

        $this->assertProductHasValues($expectedValues, $product);
    }

    public function testLoadValuesForVariantProductWithFewAttributesAfterASave()
    {
        $product = $this->findProductByIdentifier('qux');
        $this->get('pim_catalog.saver.product')->save($product);

        $expectedValues = $this->getValuesFromStandardValues(
            $this->getStandardValuesOfVariantProduct()
        );

        $this->assertProductHasValues($expectedValues, $product);
    }

    public function testItDoesNotLoadValuesOfAProductForWhichThereIsARemovedAttributeOptionOfSimpleselect()
    {
        $this->removeAttributeOptionFromAttribute('a_simple_select', 'optionB');
        $amputatedStandardValues = $this->removeAttributeFromAllStandardValues('a_simple_select');
        $expectedValues = $this->getValuesFromStandardValues(
            $amputatedStandardValues
        );
        $product = $this->findProductByIdentifier('foo');
        $this->assertProductHasValues($expectedValues, $product);
    }

    public function testItDoesNotLoadValuesOfAProductForWhichThereIsARemovedAttributeOptionOfMultiselect()
    {
        $this->removeAttributeOptionFromAttribute('a_multi_select', 'optionA');
        $this->removeAttributeOptionFromAttribute('a_multi_select', 'optionB');
        $amputatedStandardValues = $this->removeAttributeFromAllStandardValues('a_multi_select');
        $expectedValues = $this->getValuesFromStandardValues(
            $amputatedStandardValues
        );
        $product = $this->findProductByIdentifier('foo');
        $this->assertProductHasValues($expectedValues, $product);
    }

    public function testItDoesNotLoadValuesOfAProductForWhichTheFileHasBeenRemoved()
    {
        $expectedValues = $this->getValuesFromStandardValues(
            [
                'sku'    => [
                    ['locale' => null, 'scope' => null, 'data' => 'product_invalid_file'],
                ],
            ]
        );
        $product = $this->findProductByIdentifier('product_invalid_file');
        $this->assertProductHasValues($expectedValues, $product);
    }

    public function testItDoesNotLoadValuesOfAProductForWhichTheReferenceDataOptionHasBeenRemoved()
    {
        $expectedValues = $this->getValuesFromStandardValues(
            [
                'sku'    => [
                    ['locale' => null, 'scope' => null, 'data' => 'product_invalid_simple_reference_data'],
                ],
            ]
        );
        $product = $this->findProductByIdentifier('product_invalid_simple_reference_data');
        $this->assertProductHasValues($expectedValues, $product);
    }

    public function testItDoesNotLoadValuesAProductForWhichThereIsAReferenceDataCollectionOptionThatHasBeenRemoved()
    {
        $expectedValues = $this->getValuesFromStandardValues(
            [
                'sku'    => [
                    ['locale' => null, 'scope' => null, 'data' => 'product_invalid_multi_reference_data'],
                ],
                'a_ref_data_multi_select'            => [
                    ['locale' => null, 'scope' => null, 'data' => ['fabricA']]
                ],
            ]
        );
        $product = $this->findProductByIdentifier('product_invalid_multi_reference_data');
        $this->assertProductHasValues($expectedValues, $product);
    }

    /**
     * {@inheritdoc}
     */
    protected function getConfiguration()
    {
        return $this->catalog->useTechnicalSqlCatalog();
    }

    /**
     * @param string $identifier
     *
     * @return ProductInterface
     */
    private function findProductByIdentifier($identifier)
    {
        return $this->get('pim_catalog.repository.product')->findOneByIdentifier($identifier);
    }

    private function findAttributeByIdentifier(string $attributeCode): Attribute
    {
        return $this->get('akeneo.pim.structure.query.get_attributes')->forCode($attributeCode);
    }

    private function createProductValue(Attribute $attribute, ?string $scope, ?string $locale, $data)
    {
        return $this->get('akeneo.pim.enrichment.factory.value')->createByCheckingData(
            $attribute,
            $scope,
            $locale,
            $data
        );
    }

    /**
     * @param array            $expectedValues
     * @param ProductInterface $product
     */
    private function assertProductHasValues(array $expectedValues, ProductInterface $product)
    {
        $this->assertSameSize($expectedValues, $product->getValues());

        foreach ($expectedValues as $expectedValue) {
            $attributeCode = $expectedValue->getAttributeCode();
            $localeCode = $expectedValue->getLocaleCode();
            $channelCode = $expectedValue->getScopeCode();

            $actualValue = $product->getValue($attributeCode, $localeCode, $channelCode);

            $this->assertNotNull(
                $actualValue,
                sprintf(
                    'No product value found the attribute "%s", the channel "%s" and the locale "%s"',
                    $attributeCode,
                    $channelCode,
                    $localeCode
                )
            );

            $this->assertEquals($expectedValue->getData(), $actualValue->getData());
        }
    }

    /**
     * @param array $standardValues
     *
     * @return ValueInterface[]
     * @throws \Exception
     */
    private function getValuesFromStandardValues(array $standardValues)
    {
        $values = [];

        foreach ($standardValues as $attributeCode => $rawValues) {
            $attribute = $this->findAttributeByIdentifier($attributeCode);

            if (null === $attribute) {
                throw new \Exception(sprintf('No attribute found with the code "%s".', $attributeCode));
            }

            foreach ($rawValues as $rawValue) {
                $values[] = $this->createProductValue(
                    $attribute,
                    $rawValue['scope'],
                    $rawValue['locale'],
                    $rawValue['data']
                );
            }
        }

        return $values;
    }

    /**
     * @return array
     */
    private function getStandardValuesWithAllAttributes()
    {
        return [
            'sku'                                => [
                ['locale' => null, 'scope' => null, 'data' => 'foo'],
            ],
            'a_file'                             => [
                [
                    'locale' => null,
                    'scope'  => null,
                    'data'   => '8/b/5/c/8b5cf9bfd2e7e4725fd581e03251133ada1b2c99_fileA.txt',
                ],
            ],
            'an_image'                           => [
                [
                    'locale' => null,
                    'scope'  => null,
                    'data'   => '3/b/5/5/3b5548f9764c0535db2ac92f047fa448cb7cea76_imageA.jpg',
                ],
            ],
            'a_date'                             => [
                ['locale' => null, 'scope' => null, 'data' => '2016-06-13T00:00:00+02:00'],
            ],
            'a_metric'                           => [
                [
                    'locale' => null,
                    'scope'  => null,
                    'data'   => ['amount' => '987654321987.1234', 'unit' => 'KILOWATT'],
                ],
            ],
            'a_metric_without_decimal'           => [
                [
                    'locale' => null,
                    'scope'  => null,
                    'data'   => ['amount' => 98, 'unit' => 'CENTIMETER'],
                ],
            ],
            'a_metric_without_decimal_negative'  => [
                [
                    'locale' => null,
                    'scope'  => null,
                    'data'   => ['amount' => -20, 'unit' => 'CELSIUS'],
                ],
            ],
            'a_metric_negative'                  => [
                [
                    'locale' => null,
                    'scope'  => null,
                    'data'   => ['amount' => '-20.5000', 'unit' => 'CELSIUS'],
                ],
            ],
            'a_multi_select'                     => [
                ['locale' => null, 'scope' => null, 'data' => ['optionA', 'optionB']],
            ],
            'a_number_float'                     => [
                ['locale' => null, 'scope' => null, 'data' => '12.5678'],
            ],
            'a_number_float_negative'            => [
                ['locale' => null, 'scope' => null, 'data' => '-99.8732'],
            ],
            'a_number_integer'                   => [
                ['locale' => null, 'scope' => null, 'data' => 42]
            ],
            'a_number_integer_negative'          => [
                ['locale' => null, 'scope' => null, 'data' => -42]
            ],
            'a_price'                            => [
                [
                    'locale' => null,
                    'scope'  => null,
                    'data'   => [
                        ['amount' => '45.00', 'currency' => 'USD'],
                        ['amount' => '56.53', 'currency' => 'EUR'],
                    ],
                ],
            ],
            'a_price_without_decimal'            => [
                [
                    'locale' => null,
                    'scope'  => null,
                    'data'   => [
                        ['amount' => -45, 'currency' => 'USD'],
                        ['amount' => 56, 'currency' => 'EUR']
                    ],
                ],
            ],
            'a_ref_data_multi_select'            => [
                ['locale' => null, 'scope' => null, 'data' => ['fabricA', 'fabricB']]
            ],
            'a_ref_data_simple_select'           => [
                ['locale' => null, 'scope' => null, 'data' => 'colorB'],
            ],
            'a_simple_select'                    => [
                ['locale' => null, 'scope' => null, 'data' => 'optionB'],
            ],
            'a_text'                             => [
                [
                    'locale' => null,
                    'scope'  => null,
                    'data'   => 'this is a text',
                ],
            ],
            '123'                                => [
                [
                    'locale' => null,
                    'scope'  => null,
                    'data'   => 'a text for an attribute with numerical code',
                ],
            ],
            'a_text_area'                        => [
                [
                    'locale' => null,
                    'scope'  => null,
                    'data'   => 'this is a very very very very very long  text',
                ],
            ],
            'a_yes_no'                           => [
                ['locale' => null, 'scope' => null, 'data' => true],
            ],
            'a_localizable_image'                => [
                [
                    'locale' => 'en_US',
                    'scope'  => null,
                    'data'   => '7/1/3/3/713380965740f8838834cd58505aa329fcf448a5_imageB_en_US.jpg',
                ],
                [
                    'locale' => 'fr_FR',
                    'scope'  => null,
                    'data'   => '0/5/1/9/05198fcf21b2b0d4596459f172e2e62b1a70bfd0_imageB_fr_FR.jpg',
                ],
            ],
            'a_scopable_price'                   => [
                [
                    'locale' => null,
                    'scope'  => 'ecommerce',
                    'data'   => [
                        ['amount' => '20.00', 'currency' => 'USD'],
                    ],
                ],
                [
                    'locale' => null,
                    'scope'  => 'tablet',
                    'data'   => [
                        ['amount' => '17.00', 'currency' => 'EUR'],
                    ],
                ],
            ],
            'a_localized_and_scopable_text_area' => [
                [
                    'locale' => 'en_US',
                    'scope'  => 'ecommerce',
                    'data'   => 'a text area for ecommerce in English',
                ],
                [
                    'locale' => 'en_US',
                    'scope'  => 'tablet',
                    'data'   => 'a text area for tablets in English'
                ],
                [
                    'locale' => 'fr_FR',
                    'scope'  => 'tablet',
                    'data'   => 'une zone de texte pour les tablettes en français',
                ],
            ]
        ];
    }

    /**
     * @return array
     */
    private function getStandardValuesOfVariantProduct()
    {
        return [
            'a_text'                             => [
                [
                    'locale' => null,
                    'scope'  => null,
                    'data'   => 'this is a text',
                ],
            ],
            'a_simple_select'                    => [
                ['locale' => null, 'scope' => null, 'data' => 'optionB'],
            ],
            'sku'                                => [
                ['locale' => null, 'scope' => null, 'data' => 'qux'],
            ],
            'a_yes_no'                           => [
                ['locale' => null, 'scope' => null, 'data' => true],
            ],
        ];
    }

    /**
     * @param string $attributeCode
     */
    private function removeAttribute(string $attributeCode): void
    {
        $attributeToRemove = $this->get('pim_catalog.repository.attribute')->findOneByIdentifier($attributeCode);
        if (null === $attributeToRemove) {
            throw new \InvalidArgumentException(
                sprintf(
                    'Attribute not found for code "%s"',
                    $attributeCode
                )
            );
        }
        $this->get('pim_catalog.remover.attribute')->remove($attributeToRemove);
    }

    /**
     * @param string $attributeCode
     *
     * @return array
     */
    private function removeAttributeFromAllStandardValues($attributeCode): array
    {
        $allStandardValues = $this->getStandardValuesWithAllAttributes();
        unset($allStandardValues[$attributeCode]);
        return $allStandardValues;
    }

    /**
     * @param string $attributeCode
     * @param string $attributeOptionValueCode
     *
     * @throws \InvalidArgumentException
     * @throws \LogicException
     */
    private function removeAttributeOptionFromAttribute(string $attributeCode, string $attributeOptionValueCode): void
    {
        $attributeOptionIdentifier = $attributeCode . '.' . $attributeOptionValueCode;
        $attributeOptionValue = $this->get('pim_catalog.repository.attribute_option')
            ->findOneByIdentifier($attributeOptionIdentifier);
        if (null === $attributeOptionValue) {
            throw new \InvalidArgumentException(
                sprintf(
                    'Attribute option code not found in attribute "%s", "%s" given',
                    $attributeCode,
                    $attributeOptionValueCode
                )
            );
        }
        $this->get('pim_catalog.remover.attribute_option')->remove($attributeOptionValue);
    }

    /**
     * @param string $attributeCode
     *
     * @param mixed $data
     *
     * @return array
     */
    private function setDataForAttributeOfAllStandardValues(string $attributeCode, $data): array
    {
        $allStandardValues = $this->getStandardValuesWithAllAttributes();
        $allStandardValues[$attributeCode][0]['data'] = $data;
        return $allStandardValues;
    }
}
