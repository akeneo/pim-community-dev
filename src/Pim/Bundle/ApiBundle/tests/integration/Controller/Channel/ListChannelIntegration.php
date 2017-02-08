<?php

namespace Pim\Bundle\ApiBundle\tests\integration\Controller\Channel;

use Akeneo\Test\Integration\Configuration;
use Pim\Bundle\ApiBundle\tests\integration\ApiTestCase;
use Symfony\Component\HttpFoundation\Response;

class ListChannelIntegration extends ApiTestCase
{
    public function testListChannels()
    {
        $client = $this->createAuthenticatedClient();

        $client->request('GET', 'api/rest/v1/channels');

        $standardChannels = [
            '_links'       => [
                'self'  => ['href' => 'http://localhost/api/rest/v1/channels?page=1&limit=10'],
                'first' => ['href' => 'http://localhost/api/rest/v1/channels?page=1&limit=10'],
                'last'  => ['href' => 'http://localhost/api/rest/v1/channels?page=1&limit=10'],
            ],
            'current_page' => 1,
            'pages_count'  => 1,
            'items_count'  => 3,
            '_embedded'    => [
                'items' => [
                    [
                        '_links' => [
                            'self' => ['href' => 'http://localhost/api/rest/v1/channels/ecommerce']
                        ],
                        'code' => 'ecommerce',
                        'currencies' => ['USD', 'EUR'],
                        'locales' => ['en_US'],
                        'category_tree' => 'master',
                        'conversion_units' => [],
                        'labels' => [
                            'en_US' => 'Ecommerce',
                            'fr_FR' => 'Ecommerce',
                        ],
                    ],
                    [
                        '_links' => [
                            'self' => ['href' => 'http://localhost/api/rest/v1/channels/ecommerce_china']
                        ],
                        'code' => 'ecommerce_china',
                        'currencies' => ['CNY'],
                        'locales' => ['en_US', 'zh_CN'],
                        'category_tree' => 'master_china',
                        'conversion_units' => [],
                        'labels' => [
                            'en_US' => 'Ecommerce china',
                            'fr_FR' => 'Ecommerce chine',
                        ],
                    ],
                    [
                        '_links' => [
                            'self' => ['href' => 'http://localhost/api/rest/v1/channels/tablet']
                        ],
                        'code' => 'tablet',
                        'currencies' => ['USD', 'EUR'],
                        'locales' => ['de_DE', 'en_US', 'fr_FR'],
                        'category_tree' => 'master',
                        'conversion_units' => [],
                        'labels' => [
                            'en_US' => 'Tablet',
                            'fr_FR' => 'Tablette',
                        ],
                    ],
                ]
            ]
        ];

        $response = $client->getResponse();
        $this->assertSame(Response::HTTP_OK, $response->getStatusCode());

        $this->assertSame($standardChannels, json_decode($response->getContent(), true));
    }

    public function testOutOfRangeListChannels()
    {
        $client = $this->createAuthenticatedClient();

        $client->request('GET', 'api/rest/v1/channels?page=2&limit=5');

        $standardChannels = [
            '_links' => [
                'self'  => ['href' => 'http://localhost/api/rest/v1/channels?page=2&limit=5'],
                'first' => ['href' => 'http://localhost/api/rest/v1/channels?page=1&limit=5'],
                'last'  => ['href' => 'http://localhost/api/rest/v1/channels?page=1&limit=5'],
            ],
            'current_page' => 2,
            'pages_count' => 1,
            'items_count' => 3,
            '_embedded' => [
                'items' => []
            ]
        ];

        $response = $client->getResponse();
        $this->assertSame(Response::HTTP_OK, $response->getStatusCode());
        $this->assertSame($standardChannels, json_decode($response->getContent(), true));
    }

    public function testPaginationListOfChannels()
    {
        $client = $this->createAuthenticatedClient();

        $client->request('GET', 'api/rest/v1/channels?page=2&limit=2');

        $standardChannels = [
            '_links' => [
                'self'  => ['href' => 'http://localhost/api/rest/v1/channels?page=2&limit=2'],
                'first' => ['href' => 'http://localhost/api/rest/v1/channels?page=1&limit=2'],
                'last'  => ['href' => 'http://localhost/api/rest/v1/channels?page=2&limit=2'],
                'previous' => ['href' => 'http://localhost/api/rest/v1/channels?page=1&limit=2'],
            ],
            'current_page' => 2,
            'pages_count' => 2,
            'items_count' => 3,
            '_embedded' => [
                'items' => [
                    [
                        '_links' => [
                            'self' => ['href' => 'http://localhost/api/rest/v1/channels/tablet']
                        ],
                        'code' => 'tablet',
                        'currencies' => ['USD', 'EUR'],
                        'locales' => ['de_DE', 'en_US', 'fr_FR'],
                        'category_tree' => 'master',
                        'conversion_units' => [],
                        'labels' => [
                            'en_US' => 'Tablet',
                            'fr_FR' => 'Tablette',
                        ],
                    ]
                ]
            ]
        ];

        $response = $client->getResponse();
        $this->assertSame(Response::HTTP_OK, $response->getStatusCode());
        $this->assertSame($standardChannels, json_decode($response->getContent(), true));
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
