<?php

namespace tests\integration\Pim\Bundle\CatalogBundle\EventSubscriber;

use Akeneo\Test\Integration\Configuration;
use Akeneo\Test\Integration\TestCase;
use Pim\Component\Catalog\Factory\ProductValueFactory;
use Pim\Component\Catalog\Model\ProductInterface;
use Pim\Component\Catalog\Model\ProductValueInterface;
use Pim\Component\Catalog\Repository\AttributeRepositoryInterface;
use Pim\Component\Catalog\Repository\ProductRepositoryInterface;

class LoadProductValuesSubscriberIntegration extends TestCase
{
    /** @var ProductRepositoryInterface */
    private $productRepository;

    /** @var AttributeRepositoryInterface */
    private $attributeRepository;

    /** @var ProductValueFactory */
    private $valueFactory;

    public function setUp()
    {
        parent::setUp();

        $this->productRepository = $this->get('pim_catalog.repository.product');
        $this->attributeRepository = $this->get('pim_catalog.repository.attribute');
        $this->valueFactory = $this->get('pim_catalog.factory.product_value');
    }

    protected function getConfiguration()
    {
        return new Configuration(
            [Configuration::getTechnicalSqlCatalogPath()],
            false
        );
    }

    public function testLoadValuesForProductWithAllAttributes()
    {
        $product = $this->productRepository->findOneByIdentifier('foo');
        $expectedValues = $this->getValuesForProductWithAllAttributes();

        $this->assertProductHasValues($expectedValues, $product);
    }

    /**
     * @param array            $expectedValues
     * @param ProductInterface $product
     *
     * @return bool
     *
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
     * @throws \Exception
     * @return ProductValueInterface[]
     */
    private function getValuesForProductWithAllAttributes()
    {
        $values = [];

        foreach ($this->getStandardValuesWithAllAttributes() as $attributeCode => $rawValues) {
            $attribute = $this->attributeRepository->findOneByIdentifier($attributeCode);

            if (null === $attribute) {
                throw new \Exception(sprintf('No attribute found with the code "%s".', $attributeCode));
            }

            foreach ($rawValues as $rawValue) {
                $values[] = $this->valueFactory->create(
                    $attribute,
                    $rawValue['scope'],
                    $rawValue['locale'],
                    $rawValue['data']
                );
            }
        }

        return $values;
    }

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
}
