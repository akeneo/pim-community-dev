<?php

namespace Pim\Bundle\ApiBundle\tests\integration\Controller\Product;

use Akeneo\Test\Integration\Configuration;
use Symfony\Component\HttpFoundation\Response;

class GetProductIntegration extends AbstractProductTestCase
{
    public function testGetACompleteProduct()
    {
        $client = $this->createAuthenticatedClient();

        $client->request('GET', 'api/rest/v1/products/foo');

        $standardProduct = [
            'identifier'    => 'foo',
            'family'        => 'familyA',
            'groups'        => ['groupA', 'groupB'],
            'variant_group' => 'variantA',
            'categories'    => ['categoryA1', 'categoryB'],
            'enabled'       => true,
            'values'        => [
                'a_file'                             => [
                    [
                        'locale' => null,
                        'scope'  => null,
                        'data'   => '4/d/e/b/4deb535f0979dea59cf34661e22336459a56bed3_fileA.txt',
                        '_links' => [
                            'download' => [
                                'href' => 'http://localhost/api/rest/v1/media-files/4/d/e/b/4deb535f0979dea59cf34661e22336459a56bed3_fileA.txt/download'
                            ]
                        ]
                    ],
                ],
                'an_image'                           => [
                    [
                        'locale' => null,
                        'scope'  => null,
                        'data'   => '1/5/7/5/15757827125efa686c1c0f1e7930ca0c528f1c2c_imageA.jpg',
                        '_links' => [
                            'download' => [
                                'href' => 'http://localhost/api/rest/v1/media-files/1/5/7/5/15757827125efa686c1c0f1e7930ca0c528f1c2c_imageA.jpg/download'
                            ]
                        ]
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
                        '_links' => [
                            'download' => [
                                'href' => 'http://localhost/api/rest/v1/media-files/6/2/e/3/62e376e75300d27bfec78878db4d30ff1490bc53_imageB_en_US.jpg/download'
                            ]
                        ]
                    ],
                    [
                        'locale' => 'fr_FR',
                        'scope'  => null,
                        'data'   => '0/f/5/0/0f5058de76f68446bb6b2371f19cd2234b245c00_imageB_fr_FR.jpg',
                        '_links' => [
                            'download' => [
                                'href' => 'http://localhost/api/rest/v1/media-files/0/f/5/0/0f5058de76f68446bb6b2371f19cd2234b245c00_imageB_fr_FR.jpg/download'
                            ]
                        ]
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
            ]
        ];

        $response = $client->getResponse();
        $this->assertSame(Response::HTTP_OK, $response->getStatusCode());
        $this->assertResponse($response, $standardProduct);
    }

    public function testNotFoundAProduct()
    {
        $client = $this->createAuthenticatedClient();

        $client->request('GET', 'api/rest/v1/products/not_found');

        $response = $client->getResponse();
        $this->assertSame(Response::HTTP_NOT_FOUND, $response->getStatusCode());
        $content = json_decode($response->getContent(), true);
        $this->assertCount(2, $content, 'response contains 2 items');
        $this->assertSame(Response::HTTP_NOT_FOUND, $content['code']);
        $this->assertSame('Product "not_found" does not exist.', $content['message']);
    }

    /**
     * {@inheritdoc}
     */
    protected function getConfiguration()
    {
        return new Configuration([Configuration::getTechnicalSqlCatalogPath()]);
    }

    /**
     * @param Response $response
     * @param array    $expected
     */
    private function assertResponse(Response $response, array $expected)
    {
        $result = json_decode($response->getContent(), true);

        $result = $this->sanitizeDateFields($result);
        $result = $this->sanitizeMediaAttributeData($result);

        $expected = $this->sanitizeDateFields($expected);
        $expected = $this->sanitizeMediaAttributeData($expected);

        ksort($expected['values']);
        ksort($result['values']);

        $this->assertSame($expected, $result);
    }
}
