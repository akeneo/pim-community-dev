<?php

namespace tests\integration\Pim\Bundle\VersioningBundle\Normalizer\Flat;

use Akeneo\Test\Integration\MediaSanitizer;
use Pim\Bundle\VersioningBundle\tests\integration\Normalizer\Flat\AbstractFlatNormalizerTestCase;

/**
 * @author    Marie Bochu <marie.bochu@akeneo.com>
 * @copyright 2016 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class ProductIntegration extends AbstractFlatNormalizerTestCase
{
    public function testProduct()
    {
        $standardProduct = [
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
                        'data'   => $this->getFixturePath('akeneo.txt'),
                    ],
                ],
                'an_image'                           => [
                    [
                        'locale' => null,
                        'scope'  => null,
                        'data'   => $this->getFixturePath('akeneo.jpg'),
                    ],
                ],
                'a_date'                             => [
                    ['locale' => null, 'scope' => null, 'data' => '2016-06-13'],
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
                        'data'   => $this->getFixturePath('akeneo.jpg'),
                    ],
                    [
                        'locale' => 'fr_FR',
                        'scope'  => null,
                        'data'   => $this->getFixturePath('akeneo.jpg'),
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

        $saver = $this->get('pim_catalog.saver.product');
        $saver->save($this->get('pim_catalog.builder.product')->createProduct('bar'));
        $saver->save($this->get('pim_catalog.builder.product')->createProduct('baz'));

        $product = $this->get('pim_catalog.builder.product')->createProduct('foo');
        $this->get('pim_catalog.updater.product')->update($product, $standardProduct);

        $flatProduct = $this->get('pim_versioning.serializer')->normalize($product, 'flat');
        $mediaAttributes = ['a_file', 'an_image', 'a_localizable_image-en_US', 'a_localizable_image-fr_FR'];
        $flatProduct = $this->sanitizeMediaAttributeData($flatProduct, $mediaAttributes);

        $expected = [
            'sku'                                                => 'foo',
            'family'                                             => 'familyA',
            'groups'                                             => 'groupA,groupB,variantA',
            'categories'                                         => 'categoryA1,categoryB',
            'X_SELL-groups'                                      => 'groupB',
            'X_SELL-products'                                    => 'bar',
            'UPSELL-groups'                                      => 'groupA',
            'UPSELL-products'                                    => '',
            'SUBSTITUTION-groups'                                => '',
            'SUBSTITUTION-products'                              => '',
            'PACK-groups'                                        => '',
            'PACK-products'                                      => 'bar,baz',
            'a_date'                                             => '2016-06-13',
            'a_file'                                             => '4/d/e/b/4deb535f0979dea59cf34661e22336459a56bed3_fileA.txt',
            'a_localizable_image-en_US'                          => '6/2/e/3/62e376e75300d27bfec78878db4d30ff1490bc53_imageB_en_US.jpg',
            'a_localizable_image-fr_FR'                          => '0/f/5/0/0f5058de76f68446bb6b2371f19cd2234b245c00_imageB_fr_FR.jpg',
            'a_localized_and_scopable_text_area-en_US-ecommerce' => 'a text area for ecommerce in English',
            'a_localized_and_scopable_text_area-en_US-tablet'    => 'a text area for tablets in English',
            'a_localized_and_scopable_text_area-fr_FR-tablet'    => 'une zone de texte pour les tablettes en français',
            'a_metric'                                           => '987654321987.1234',
            'a_metric-unit'                                      => 'KILOWATT',
            'a_metric_negative'                                  => '-20.5000',
            'a_metric_negative-unit'                             => 'CELSIUS',
            'a_metric_without_decimal'                           => '98',
            'a_metric_without_decimal-unit'                      => 'CENTIMETER',
            'a_metric_without_decimal_negative'                  => '-20',
            'a_metric_without_decimal_negative-unit'             => 'CELSIUS',
            'a_multi_select'                                     => 'optionA,optionB',
            'a_number_float'                                     => '12.5678',
            'a_number_float_negative'                            => '-99.8732',
            'a_number_integer'                                   => '42',
            'a_number_integer_negative'                          => '-42',
            'a_price-EUR'                                        => '56.53',
            'a_price-USD'                                        => '45.00',
            'a_price_without_decimal-EUR'                        => '56.00',
            'a_price_without_decimal-USD'                        => '-45.00',
            'a_scopable_price-ecommerce-EUR'                     => '15.00',
            'a_scopable_price-ecommerce-USD'                     => '20.00',
            'a_scopable_price-tablet-EUR'                        => '17.00',
            'a_scopable_price-tablet-USD'                        => '24.00',
            'a_simple_select'                                    => 'optionB',
            'a_text'                                             => 'A name',
            'a_text_area'                                        => 'this is a very very very very very long  text',
            'a_yes_no'                                           => '1',
            'an_image'                                           => '1/5/7/5/15757827125efa686c1c0f1e7930ca0c528f1c2c_imageA.jpg',
            'enabled'                                            => 1,
        ];

        $expected = $this->sanitizeMediaAttributeData($expected, $mediaAttributes);

        $this->assertSame($flatProduct, $expected);
    }

    /**
     * Replaces media attributes data in the $data array by self::MEDIA_ATTRIBUTE_DATA_COMPARISON.
     *
     * @param array $data
     * @param array $mediaAttributes
     *
     * @return array
     */
    private function sanitizeMediaAttributeData(array $data, array $mediaAttributes)
    {
        foreach ($data as $attribute => $value) {
            if (in_array($attribute, $mediaAttributes)) {
                $data[$attribute] = MediaSanitizer::sanitize($value);
            }
        }

        return $data;
    }
}
