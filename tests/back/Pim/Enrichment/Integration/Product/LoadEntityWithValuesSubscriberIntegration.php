<?php

namespace AkeneoTest\Pim\Enrichment\Integration\Product;

use Akeneo\Pim\Enrichment\Component\Product\Model\Product;
use Akeneo\Pim\Enrichment\Component\Product\Model\ProductInterface;
use Akeneo\Pim\Enrichment\Component\Product\Model\ValueInterface;
use Akeneo\Pim\Enrichment\Component\Product\Value\OptionsValue;
use Akeneo\Pim\Structure\Component\Query\PublicApi\AttributeType\Attribute;
use Akeneo\Test\Integration\TestCase;
use PHPUnit\Framework\Assert;

/**
 * Integration tests to verify a product is well loaded from database with its product values.
 */
class LoadEntityWithValuesSubscriberIntegration extends TestCase
{
    public function updateFooProduct()
    {
        $sql = <<<SQL
UPDATE pim_catalog_product SET raw_values = '{\"sku\": {\"<all_channels>\": {\"<all_locales>\": \"foo\"}},\"a_file\":{\"<all_channels>\":{\"<all_locales>\":\"8/b/5/c/8b5cf9bfd2e7e4725fd581e03251133ada1b2c99_fileA.txt\"}},\"an_image\":{\"<all_channels>\":{\"<all_locales>\":\"3/b/5/5/3b5548f9764c0535db2ac92f047fa448cb7cea76_imageA.jpg\"}},\"a_date\":{\"<all_channels>\":{\"<all_locales>\":\"2016-06-13T00:00:00+02:00\"}},\"a_metric\":{\"<all_channels>\":{\"<all_locales>\":{\"amount\":\"987654321987.1234\",\"unit\":\"KILOWATT\",\"base_data\":\"987654321987123.4000\",\"base_unit\":\"WATT\",\"family\":\"Power\"}}},\"a_metric_without_decimal\":{\"<all_channels>\":{\"<all_locales>\":{\"amount\":98,\"unit\":\"CENTIMETER\",\"base_data\":\"0.98\",\"base_unit\":\"METER\",\"family\":\"Length\"}}},\"a_metric_without_decimal_negative\":{\"<all_channels>\":{\"<all_locales>\":{\"amount\":-20,\"unit\":\"CELSIUS\",\"base_data\":\"253.150000000000\",\"base_unit\":\"KELVIN\",\"family\":\"Temperature\"}}},\"a_metric_negative\":{\"<all_channels>\":{\"<all_locales>\":{\"amount\":\"-20.5000\",\"unit\":\"CELSIUS\",\"base_data\":\"252.650000000000\",\"base_unit\":\"KELVIN\",\"family\":\"Temperature\"}}},\"a_multi_select\":{\"<all_channels>\":{\"<all_locales>\":[\"optionA\",\"optionB\"]}},\"a_number_float\":{\"<all_channels>\":{\"<all_locales>\":\"12.56781111111\"}},\"a_number_float_negative\":{\"<all_channels>\":{\"<all_locales>\":\"-99.87321111111\"}},\"a_number_integer\":{\"<all_channels>\":{\"<all_locales>\":42}},\"a_number_integer_negative\":{\"<all_channels>\":{\"<all_locales>\":-42}},\"a_price\":{\"<all_channels>\":{\"<all_locales>\":[{\"amount\":\"45.00\",\"currency\":\"USD\"},{\"amount\":\"56.53\",\"currency\":\"EUR\"}]}},\"a_price_without_decimal\":{\"<all_channels>\":{\"<all_locales>\":[{\"amount\":-45,\"currency\":\"USD\"},{\"amount\":56,\"currency\":\"EUR\"}]}},\"a_ref_data_multi_select\":{\"<all_channels>\":{\"<all_locales>\":[\"fabricA\",\"fabricB\"]}},\"a_ref_data_simple_select\":{\"<all_channels>\":{\"<all_locales>\":\"colorB\"}},\"a_simple_select\":{\"<all_channels>\":{\"<all_locales>\":\"optionB\"}},\"a_text\":{\"<all_channels>\":{\"<all_locales>\":\"this is a text\"}},\"123\":{\"<all_channels>\":{\"<all_locales>\":\"a text for an attribute with numerical code\"}},\"a_text_area\":{\"<all_channels>\":{\"<all_locales>\":\"this is a very very very very very long  text\"}},\"a_yes_no\":{\"<all_channels>\":{\"<all_locales>\":true}},\"a_localizable_image\":{\"<all_channels>\":{\"en_US\":\"7/1/3/3/713380965740f8838834cd58505aa329fcf448a5_imageB_en_US.jpg\",\"fr_FR\":\"0/5/1/9/05198fcf21b2b0d4596459f172e2e62b1a70bfd0_imageB_fr_FR.jpg\"}},\"a_scopable_price\":{\"ecommerce\":{\"<all_locales>\":[{\"amount\":\"15.00\",\"currency\":\"EUR\"},{\"amount\":\"20.00\",\"currency\":\"USD\"}]},\"tablet\":{\"<all_locales>\":[{\"amount\":\"17.00\",\"currency\":\"EUR\"},{\"amount\":\"24.00\",\"currency\":\"USD\"}]}},\"a_localized_and_scopable_text_area\":{\"ecommerce\":{\"en_US\":\"a text area for ecommerce in English\"},\"tablet\":{\"en_US\":\"a text area for tablets in English\",\"fr_FR\":\"une zone de texte pour les tablettes en français\"}}}'
WHERE identifier = 'foo' ;
SQL;
        $this->get('database_connection')->executeQuery($sql);
    }

    public function testLoadValuesForProductWithAllAttributes()
    {
        $this->updateFooProduct();
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
        $this->updateFooProduct();

        $this->clearAttributeOptionsCache();
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
        $this->updateFooProduct();

        $this->clearAttributeOptionsCache();
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

    public function test_it_does_not_load_duplicate_multiselect_options()
    {
        // save a product without validation to get duplicate options in database
        $productWithDuplicateOptions = $this->get('pim_catalog.builder.product')->createProduct(
            'product_with_duplicate_options'
        );
        $productWithDuplicateOptions->addValue(
            OptionsValue::value('a_multi_select', ['optionA', 'OPTIONA', 'optionb', 'OptionB'])
        );
        $this->get('pim_catalog.saver.product')->save($productWithDuplicateOptions);
        $rawValuesInDb = $this->get('database_connection')->executeQuery(
            'SELECT JSON_EXTRACT(raw_values, "$.a_multi_select") from pim_catalog_product where identifier = :identifier',
            ['identifier' => 'product_with_duplicate_options'],
        )->fetchColumn();
        Assert::assertEquals(
            ['optionA', 'OPTIONA', 'optionb', 'OptionB'],
            \json_decode($rawValuesInDb, true)['<all_channels>']['<all_locales>'] ?? null
        );

        $this->get('pim_connector.doctrine.cache_clearer')->clear();
        $product = $this->get('pim_catalog.repository.product')->findOneByIdentifier('product_with_duplicate_options');

        Assert::assertSame(['optionA', 'optionB'], $product->getValue('a_multi_select')->getData());
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
                ['locale' => null, 'scope' => null, 'data' => '12.56781111111'],
            ],
            'a_number_float_negative'            => [
                ['locale' => null, 'scope' => null, 'data' => '-99.87321111111'],
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
     * We need to clear the LRU cache because it's not meant to be used twice in the same request
     */
    private function clearAttributeOptionsCache(): void
    {
        $this->get('akeneo.pim.structure.query.cache.get_existing_attribute_option_codes_from_option_codes')
            ->clear();
    }
}
