<?php

namespace tests\integration\Pim\Bundle\CatalogBundle\EventSubscriber;

use Akeneo\Test\Integration\Configuration;
use Akeneo\Test\Integration\TestCase;
use Pim\Component\Catalog\Model\AttributeInterface;
use Pim\Component\Catalog\Model\ProductInterface;
use Pim\Component\Catalog\Model\ValueInterface;

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

    /**
     * {@inheritdoc}
     */
    protected function getConfiguration()
    {
        return new Configuration([Configuration::getTechnicalSqlCatalogPath()]);
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

    /**
     * @param string $attributeCode
     *
     * @return AttributeInterface
     */
    private function findAttributeByIdentifier($attributeCode)
    {
        return $this->get('pim_catalog.repository.attribute')->findOneByIdentifier($attributeCode);
    }

    /**
     * @param AttributeInterface $attribute
     * @param string             $scope
     * @param string             $locale
     * @param mixed              $data
     *
     * @return ValueInterface
     */
    private function createProductValue(AttributeInterface $attribute, $scope, $locale, $data)
    {
        return $this->get('pim_catalog.factory.value')->create(
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
            $attribute = $expectedValue->getAttribute();
            $localeCode = $expectedValue->getLocale();
            $channelCode = $expectedValue->getScope();

            $actualValue = $product->getValue($attribute->getCode(), $localeCode, $channelCode);

            $this->assertNotNull(
                $actualValue,
                sprintf(
                    'No product value found the attribute "%s", the channel "%s" and the locale "%s"',
                    $attribute,
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
                        ['amount' => '56.53', 'currency' => 'EUR']
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
                        ['amount' => '15.00', 'currency' => 'EUR'],
                        ['amount' => '20.00', 'currency' => 'USD'],
                    ],
                ],
                [
                    'locale' => null,
                    'scope'  => 'tablet',
                    'data'   => [
                        ['amount' => '17.00', 'currency' => 'EUR'],
                        ['amount' => '24.00', 'currency' => 'USD'],
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
}
