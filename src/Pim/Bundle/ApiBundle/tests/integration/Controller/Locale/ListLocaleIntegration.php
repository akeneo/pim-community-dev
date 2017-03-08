<?php

namespace Pim\Bundle\ApiBundle\tests\integration\Controller\Locale;

use Akeneo\Test\Integration\Configuration;
use Pim\Bundle\ApiBundle\tests\integration\ApiTestCase;
use Symfony\Component\HttpFoundation\Response;

class ListLocaleIntegration extends ApiTestCase
{
    public function testListLocales()
    {
        $client = $this->createAuthenticatedClient();

        $client->request('GET', 'api/rest/v1/locales');

        $apiLocales = [
            '_links'       => [
                'self'  => ['href' => 'http://localhost/api/rest/v1/locales?page=1&limit=10'],
                'first' => ['href' => 'http://localhost/api/rest/v1/locales?page=1&limit=10'],
                'last'  => ['href' => 'http://localhost/api/rest/v1/locales?page=2&limit=10'],
                'next'  => ['href' => 'http://localhost/api/rest/v1/locales?page=2&limit=10'],
            ],
            'current_page' => 1,
            'pages_count'  => 2,
            'items_count'  => 15,
            '_embedded'    => [
                'items' => [
                    [
                        '_links' => [
                            'self' => ['href' => 'http://localhost/api/rest/v1/locales/af_ZA']
                        ],
                        'code'    => 'af_ZA',
                        'enabled' => false,
                    ],
                    [
                        '_links' => [
                            'self' => ['href' => 'http://localhost/api/rest/v1/locales/de_CH']
                        ],
                        'code'    => 'de_CH',
                        'enabled' => false,
                    ],
                    [
                        '_links' => [
                            'self' => ['href' => 'http://localhost/api/rest/v1/locales/de_DE']
                        ],
                        'code'    => 'de_DE',
                        'enabled' => true,
                    ],
                    [
                        '_links' => [
                            'self' => ['href' => 'http://localhost/api/rest/v1/locales/en_GB']
                        ],
                        'code'    => 'en_GB',
                        'enabled' => false,
                    ],
                    [
                        '_links' => [
                            'self' => ['href' => 'http://localhost/api/rest/v1/locales/en_US']
                        ],
                        'code'    => 'en_US',
                        'enabled' => true,
                    ],
                    [
                        '_links' => [
                            'self' => ['href' => 'http://localhost/api/rest/v1/locales/es_ES']
                        ],
                        'code'    => 'es_ES',
                        'enabled' => false,
                    ],
                    [
                        '_links' => [
                            'self' => ['href' => 'http://localhost/api/rest/v1/locales/fr_BE']
                        ],
                        'code'    => 'fr_BE',
                        'enabled' => false,
                    ],
                    [
                        '_links' => [
                            'self' => ['href' => 'http://localhost/api/rest/v1/locales/fr_CA']
                        ],
                        'code'    => 'fr_CA',
                        'enabled' => false,
                    ],
                    [
                        '_links' => [
                            'self' => ['href' => 'http://localhost/api/rest/v1/locales/fr_CH']
                        ],
                        'code'    => 'fr_CH',
                        'enabled' => false,
                    ],
                    [
                        '_links' => [
                            'self' => ['href' => 'http://localhost/api/rest/v1/locales/fr_FR']
                        ],
                        'code'    => 'fr_FR',
                        'enabled' => true,
                    ],
                ]
            ]
        ];

        $response = $client->getResponse();
        $this->assertSame(Response::HTTP_OK, $response->getStatusCode());

        $this->assertSame($apiLocales, json_decode($response->getContent(), true));
    }

    public function testOutOfRangeListLocales()
    {
        $client = $this->createAuthenticatedClient();

        $client->request('GET', 'api/rest/v1/locales?page=25&limit=12');

        $apiLocales = [
            '_links' => [
                'self'  => ['href' => 'http://localhost/api/rest/v1/locales?page=25&limit=12'],
                'first' => ['href' => 'http://localhost/api/rest/v1/locales?page=1&limit=12'],
                'last'  => ['href' => 'http://localhost/api/rest/v1/locales?page=2&limit=12'],
            ],
            'current_page' => 25,
            'pages_count'  => 2,
            'items_count'  => 15,
            '_embedded'    => [
                'items' => []
            ]
        ];

        $response = $client->getResponse();
        $this->assertSame(Response::HTTP_OK, $response->getStatusCode());
        $this->assertSame($apiLocales, json_decode($response->getContent(), true));
    }

    public function testPaginationListOfLocales()
    {
        $client = $this->createAuthenticatedClient();

        $client->request('GET', 'api/rest/v1/locales?page=2&limit=5');

        $apiLocales = [
            '_links' => [
                'self'     => ['href' => 'http://localhost/api/rest/v1/locales?page=2&limit=5'],
                'first'    => ['href' => 'http://localhost/api/rest/v1/locales?page=1&limit=5'],
                'last'     => ['href' => 'http://localhost/api/rest/v1/locales?page=3&limit=5'],
                'previous' => ['href' => 'http://localhost/api/rest/v1/locales?page=1&limit=5'],
                'next'     => ['href' => 'http://localhost/api/rest/v1/locales?page=3&limit=5']
            ],
            'current_page' => 2,
            'pages_count'  => 3,
            'items_count'  => 15,
            '_embedded'    => [
                'items' => [
                    [
                        '_links' => [
                            'self' => ['href' => 'http://localhost/api/rest/v1/locales/es_ES'],
                        ],
                        'code'    => 'es_ES',
                        'enabled' => false
                    ],
                    [
                        '_links' => [
                            'self' => ['href' => 'http://localhost/api/rest/v1/locales/fr_BE']
                        ],
                        'code'    => 'fr_BE',
                        'enabled' => false
                    ],
                    [
                        '_links' => [
                            'self' => ['href' => 'http://localhost/api/rest/v1/locales/fr_CA']
                        ],
                        'code'    => 'fr_CA',
                        'enabled' => false
                    ],
                    [
                        '_links' => [
                            'self' => ['href' => 'http://localhost/api/rest/v1/locales/fr_CH']
                        ],
                        'code'    => 'fr_CH',
                        'enabled' => false
                    ],
                    [
                        '_links' => [
                            'self' => ['href' => 'http://localhost/api/rest/v1/locales/fr_FR']
                        ],
                        'code'    => 'fr_FR',
                        'enabled' => true
                    ]
                ]
            ]
        ];

        $response = $client->getResponse();
        $this->assertSame(Response::HTTP_OK, $response->getStatusCode());
        $this->assertSame($apiLocales, json_decode($response->getContent(), true));
    }

    /**
     * {@inheritdoc}
     */
    protected function getConfiguration()
    {
        return new Configuration(
            [Configuration::getTechnicalCatalogPath()],
            false
        );
    }
}
