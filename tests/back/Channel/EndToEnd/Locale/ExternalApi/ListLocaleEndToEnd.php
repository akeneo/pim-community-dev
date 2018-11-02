<?php

namespace AkeneoTest\Channel\EndToEnd\Locale\ExternalApi;

use Akeneo\Tool\Bundle\ApiBundle\tests\integration\ApiTestCase;
use Symfony\Component\HttpFoundation\Response;

class ListLocaleEndToEnd extends ApiTestCase
{
    public function testListLocales()
    {
        $client = $this->createAuthenticatedClient();

        $client->request('GET', 'api/rest/v1/locales');

        $apiLocales = [
            '_links'       => [
                'self'  => ['href' => 'http://localhost/api/rest/v1/locales?page=1&limit=10&with_count=false'],
                'first' => ['href' => 'http://localhost/api/rest/v1/locales?page=1&limit=10&with_count=false'],
                'next'  => ['href' => 'http://localhost/api/rest/v1/locales?page=2&limit=10&with_count=false'],
            ],
            'current_page' => 1,
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
            '_links'       => [
                'self'     => ['href' => 'http://localhost/api/rest/v1/locales?page=25&limit=12&with_count=false'],
                'first'    => ['href' => 'http://localhost/api/rest/v1/locales?page=1&limit=12&with_count=false'],
                'previous' => ['href' => 'http://localhost/api/rest/v1/locales?page=24&limit=12&with_count=false'],
            ],
            'current_page' => 25,
            '_embedded'    => [
                'items' => [],
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
                'self'     => ['href' => 'http://localhost/api/rest/v1/locales?page=2&limit=5&with_count=false'],
                'first'    => ['href' => 'http://localhost/api/rest/v1/locales?page=1&limit=5&with_count=false'],
                'previous' => ['href' => 'http://localhost/api/rest/v1/locales?page=1&limit=5&with_count=false'],
                'next'     => ['href' => 'http://localhost/api/rest/v1/locales?page=3&limit=5&with_count=false']
            ],
            'current_page' => 2,
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

    public function testListOfLocalesWithCount()
    {
        $client = $this->createAuthenticatedClient();

        $client->request('GET', 'api/rest/v1/locales?page=2&limit=2&with_count=true');

        $apiLocales = [
            '_links' => [
                'self'     => ['href' => 'http://localhost/api/rest/v1/locales?page=2&limit=2&with_count=true'],
                'first'    => ['href' => 'http://localhost/api/rest/v1/locales?page=1&limit=2&with_count=true'],
                'previous' => ['href' => 'http://localhost/api/rest/v1/locales?page=1&limit=2&with_count=true'],
                'next'     => ['href' => 'http://localhost/api/rest/v1/locales?page=3&limit=2&with_count=true']
            ],
            'current_page' => 2,
            'items_count' => 15,
            '_embedded'    => [
                'items' => [
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
        return $this->catalog->useTechnicalCatalog();
    }
}
