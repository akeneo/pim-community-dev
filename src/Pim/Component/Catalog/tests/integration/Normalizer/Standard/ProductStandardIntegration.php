<?php

namespace tests\integration\Pim\Component\Catalog\Normalizer\Standard;

use Akeneo\Test\Integration\Configuration;
use Akeneo\Test\Integration\DateSanitizer;
use Akeneo\Test\Integration\MediaSanitizer;
use Akeneo\Test\Integration\TestCase;
use Pim\Component\Catalog\Model\ProductInterface;

/**
 * Integration tests to verify data from database are well formatted in the standard format
 */
class ProductStandardIntegration extends TestCase
{
    /**
     * {@inheritdoc}
     */
    protected function getConfiguration()
    {
        return new Configuration([Configuration::getTechnicalSqlCatalogPath()]);
    }

    public function testEmptyDisabledProduct()
    {
        $expected = [
            'identifier'    => 'bar',
            'family'        => null,
            'groups'        => [],
            'variant_group' => null,
            'categories'    => [],
            'enabled'       => false,
            'values'        => [
                'sku' => [
                    0 => [
                        'locale' => null,
                        'scope'  => null,
                        'data'   => 'bar',
                    ],
                ],
            ],
            'created'       => '2016-06-14T13:12:50+02:00',
            'updated'       => '2016-06-14T13:12:50+02:00',
            'associations'  => [],
        ];

        $this->assertStandardFormat('bar', $expected);
    }

    public function testEmptyEnabledProduct()
    {
        $expected = [
            'identifier'    => 'baz',
            'family'        => null,
            'groups'        => [],
            'variant_group' => null,
            'categories'    => [],
            'enabled'       => true,
            'values'        => [
                'sku' => [
                    0 => [
                        'locale' => null,
                        'scope'  => null,
                        'data'   => 'baz',
                    ],
                ],
            ],
            'created'       => '2016-06-14T13:12:50+02:00',
            'updated'       => '2016-06-14T13:12:50+02:00',
            'associations'  => [],
        ];

        $this->assertStandardFormat('baz', $expected);
    }

    public function testProductWithAllAttributes()
    {
        $expected =
            [
                'identifier'    => 'foo',
                'family'        => 'familyA',
                'groups'        => ['groupA', 'groupB'],
                'variant_group' => 'variantA',
                'categories'    => ['categoryA1', 'categoryB'],
                'enabled'       => true,
                'values'        => [
                    'sku'                                => [
                        ['locale' => null, 'scope' => null, 'data' => 'foo'],
                    ],
                    'a_file'                             => [
                        [
                            'locale' => null,
                            'scope'  => null,
                            'data'   => '4/d/e/b/4deb535f0979dea59cf34661e22336459a56bed3_fileA.txt',
                        ],
                    ],
                    'an_image'                           => [
                        [
                            'locale' => null,
                            'scope'  => null,
                            'data'   => '1/5/7/5/15757827125efa686c1c0f1e7930ca0c528f1c2c_imageA.jpg',
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
                    'a_metric_without_decimal' => [
                        [
                            'locale' => null,
                            'scope'  => null,
                            'data'   => ['amount' => 98, 'unit' => 'CENTIMETER'],
                        ],
                    ],
                    'a_metric_without_decimal_negative' => [
                        [
                            'locale' => null,
                            'scope'  => null,
                            'data'   => ['amount' => -20, 'unit' => 'CELSIUS'],
                        ],
                    ],
                    'a_metric_negative'        => [
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
                    'a_number_integer_negative' => [
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
                            'data'   => '6/2/e/3/62e376e75300d27bfec78878db4d30ff1490bc53_imageB_en_US.jpg',
                        ],
                        [
                            'locale' => 'fr_FR',
                            'scope'  => null,
                            'data'   => '0/f/5/0/0f5058de76f68446bb6b2371f19cd2234b245c00_imageB_fr_FR.jpg',
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
                    ],
                ],
                'created'       => '2016-06-14T13:12:50+02:00',
                'updated'       => '2016-06-14T13:12:50+02:00',
                'associations'  => [
                    'PACK'   => ['groups' => [], 'products' => ['bar', 'baz']],
                    'UPSELL' => ['groups' => ['groupA'], 'products' => []],
                    'X_SELL' => ['groups' => ['groupB'], 'products' => ['bar']],
                ],
            ];

        $this->assertStandardFormat('foo', $expected);
    }

    /**
     * @param string $identifier
     * @param array  $expected
     */
    private function assertStandardFormat($identifier, array $expected)
    {
        $repository = $this->get('pim_catalog.repository.product');
        $product = $repository->findOneByIdentifier($identifier);

        $result = $this->normalizeProductToStandardFormat($product);
        $result = $this->sanitizeDateFields($result);
        $result = $this->sanitizeMediaAttributeData($result);

        $expected = $this->sanitizeDateFields($expected);
        $expected = $this->sanitizeMediaAttributeData($expected);

        ksort($expected['values']);
        ksort($result['values']);

        $this->assertSame($expected, $result);
    }

    /**
     * @param ProductInterface $product
     *
     * @return array
     */
    private function normalizeProductToStandardFormat(ProductInterface $product)
    {
        $serializer = $this->get('pim_serializer');

        return $serializer->normalize($product, 'standard');
    }

    /**
     * Replaces dates fields (created/updated) in the $data array by self::DATE_FIELD_COMPARISON.
     *
     * @param array $data
     *
     * @return array
     */
    private function sanitizeDateFields(array $data)
    {
        $data['created'] = DateSanitizer::sanitize($data['created']);
        $data['updated'] = DateSanitizer::sanitize($data['updated']);

        return $data;
    }

    /**
     * Replaces media attributes data in the $data array by self::MEDIA_ATTRIBUTE_DATA_COMPARISON.
     *
     * @param array $data
     *
     * @return array
     */
    private function sanitizeMediaAttributeData(array $data)
    {
        foreach ($data['values'] as $attributeCode => $values) {
            if (1 === preg_match('/.*(file|image).*/', $attributeCode)) {
                foreach ($values as $index => $value) {
                    $data['values'][$attributeCode][$index]['data'] = MediaSanitizer::sanitize($value['data']);
                }
            }
        }

        return $data;
    }
}
