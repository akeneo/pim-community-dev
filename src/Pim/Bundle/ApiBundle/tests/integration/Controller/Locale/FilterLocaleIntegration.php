<?php

namespace Pim\Bundle\ApiBundle\tests\integration\Controller\Locale;

use Akeneo\Test\Integration\Configuration;
use Pim\Bundle\ApiBundle\tests\integration\ApiTestCase;
use Symfony\Component\HttpFoundation\Response;

class FilterLocaleIntegration extends ApiTestCase
{
    public function testFilterActivatedLocales()
    {
        $client = $this->createAuthenticatedClient();

        $filters = [
            'enabled' => [['operator' => '=', 'value' => true]]
        ];
        $searchString = urlencode(json_encode($filters));
        $filterLocaleUrl = sprintf('api/rest/v1/locales?search=%s', $searchString);
        $client->request('GET', $filterLocaleUrl);

        $standardLocales = [
            '_links'       => [
                'self'  => [
                    'href' => sprintf('http://localhost/api/rest/v1/locales?search=%s&page=1&limit=10', $searchString)
                ],
                'first' => [
                    'href' => sprintf('http://localhost/api/rest/v1/locales?search=%s&page=1&limit=10', $searchString)
                ],
                'last'  => [
                    'href' => sprintf('http://localhost/api/rest/v1/locales?search=%s&page=1&limit=10', $searchString)
                ],
            ],
            'current_page' => 1,
            'pages_count'  => 1,
            'items_count'  => 4,
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
                            'self' => ['href' => 'http://localhost/api/rest/v1/locales/en_US']
                        ],
                        'code'    => 'en_US',
                        'enabled' => true,
                    ],
                    [
                        '_links' => [
                            'self' => ['href' => 'http://localhost/api/rest/v1/locales/fr_FR']
                        ],
                        'code'    => 'fr_FR',
                        'enabled' => true,
                    ],
                    [
                        '_links' => [
                            'self' => ['href' => 'http://localhost/api/rest/v1/locales/zh_CN']
                        ],
                        'code'    => 'zh_CN',
                        'enabled' => true,
                    ],
                ]
            ]
        ];

        $response = $client->getResponse();
        $this->assertSame(Response::HTTP_OK, $response->getStatusCode());

        $this->assertSame($standardLocales, json_decode($response->getContent(), true));
    }

    public function testFilterDeactivatedLocales()
    {
        $client = $this->createAuthenticatedClient();

        $filters = [
            'enabled' => [['operator' => '=', 'value' => false]]
        ];
        $searchString = urlencode(json_encode($filters));
        $filterLocaleUrl = sprintf('api/rest/v1/locales?search=%s', $searchString);
        $client->request('GET', $filterLocaleUrl);

        $standardLocales = [
            '_links'       => [
                'self'  => [
                    'href' => sprintf('http://localhost/api/rest/v1/locales?search=%s&page=1&limit=10', $searchString)
                ],
                'first' => [
                    'href' => sprintf('http://localhost/api/rest/v1/locales?search=%s&page=1&limit=10', $searchString)
                ],
                'last'  => [
                    'href' => sprintf('http://localhost/api/rest/v1/locales?search=%s&page=2&limit=10', $searchString)
                ],
                'next'  => [
                    'href' => sprintf('http://localhost/api/rest/v1/locales?search=%s&page=2&limit=10', $searchString)
                ],
            ],
            'current_page' => 1,
            'pages_count'  => 2,
            'items_count'  => 11,
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
                            'self' => ['href' => 'http://localhost/api/rest/v1/locales/en_GB']
                        ],
                        'code'    => 'en_GB',
                        'enabled' => false,
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
                            'self' => ['href' => 'http://localhost/api/rest/v1/locales/fr_LU']
                        ],
                        'code'    => 'fr_LU',
                        'enabled' => false,
                    ],
                    [
                        '_links' => [
                            'self' => ['href' => 'http://localhost/api/rest/v1/locales/fr_MC']
                        ],
                        'code'    => 'fr_MC',
                        'enabled' => false,
                    ],
                    [
                        '_links' => [
                            'self' => ['href' => 'http://localhost/api/rest/v1/locales/fy_NL']
                        ],
                        'code'    => 'fy_NL',
                        'enabled' => false,
                    ],
                ]
            ]
        ];

        $response = $client->getResponse();
        $this->assertSame(Response::HTTP_OK, $response->getStatusCode());

        $this->assertSame($standardLocales, json_decode($response->getContent(), true));
    }

    public function testFilterOnNonAuthorizedField()
    {
        $client = $this->createAuthenticatedClient();

        $filters = [
            'non_authorized' => [['operator' => '=', 'value' => false]]
        ];
        $searchString = urlencode(json_encode($filters));
        $filterLocaleUrl = sprintf('api/rest/v1/locales?search=%s', $searchString);
        $client->request('GET', $filterLocaleUrl);

        $response = $client->getResponse();
        $this->assertSame(Response::HTTP_UNPROCESSABLE_ENTITY, $response->getStatusCode());
        $content = json_decode($response->getContent(), true);
        $this->assertCount(2, $content, 'response contains 2 items');
        $this->assertSame(Response::HTTP_UNPROCESSABLE_ENTITY, $content['code']);
        $this->assertSame(
            'Filter on property "non_authorized" is not supported or does not support operator "=".',
            $content['message']
        );
    }

    public function testFilterOnInvalidJson()
    {
        $client = $this->createAuthenticatedClient();

        $searchString = urlencode('{"enabled":[{"operator":"=","value\': "]}');
        $filterLocaleUrl = sprintf('api/rest/v1/locales?search=%s', $searchString);
        $client->request('GET', $filterLocaleUrl);

        $response = $client->getResponse();
        $this->assertSame(Response::HTTP_BAD_REQUEST, $response->getStatusCode());
        $content = json_decode($response->getContent(), true);
        $this->assertCount(2, $content, 'response contains 2 items');
        $this->assertSame(Response::HTTP_BAD_REQUEST, $content['code']);
        $this->assertSame('Search query parameter should be valid JSON.', $content['message']);
    }

    public function testFilterMissingOperatorAndValueFields()
    {
        $client = $this->createAuthenticatedClient();

        $filters = [
            'enabled' => []
        ];
        $searchString = urlencode(json_encode($filters));
        $filterLocaleUrl = sprintf('api/rest/v1/locales?search=%s', $searchString);
        $client->request('GET', $filterLocaleUrl);

        $response = $client->getResponse();
        $this->assertSame(Response::HTTP_UNPROCESSABLE_ENTITY, $response->getStatusCode());
        $content = json_decode($response->getContent(), true);
        $this->assertCount(2, $content, 'response contains 2 items');
        $this->assertSame(Response::HTTP_UNPROCESSABLE_ENTITY, $content['code']);
        $this->assertSame('Operator and value are missing for the property "enabled".', $content['message']);
    }

    public function testFilterMissingOperatorField()
    {
        $client = $this->createAuthenticatedClient();

        $filters = [
            'enabled' => [['value' => true]]
        ];
        $searchString = urlencode(json_encode($filters));
        $filterLocaleUrl = sprintf('api/rest/v1/locales?search=%s', $searchString);
        $client->request('GET', $filterLocaleUrl);

        $response = $client->getResponse();
        $this->assertSame(Response::HTTP_UNPROCESSABLE_ENTITY, $response->getStatusCode());
        $content = json_decode($response->getContent(), true);
        $this->assertCount(2, $content, 'response contains 2 items');
        $this->assertSame(Response::HTTP_UNPROCESSABLE_ENTITY, $content['code']);
        $this->assertSame('Operator is missing for the property "enabled".', $content['message']);
    }

    public function testFilterMissingValueField()
    {
        $client = $this->createAuthenticatedClient();

        $filters = [
            'enabled' => [['operator' => '=']]
        ];
        $searchString = urlencode(json_encode($filters));
        $filterLocaleUrl = sprintf('api/rest/v1/locales?search=%s', $searchString);
        $client->request('GET', $filterLocaleUrl);

        $response = $client->getResponse();
        $this->assertSame(Response::HTTP_UNPROCESSABLE_ENTITY, $response->getStatusCode());
        $content = json_decode($response->getContent(), true);
        $this->assertCount(2, $content, 'response contains 2 items');
        $this->assertSame(Response::HTTP_UNPROCESSABLE_ENTITY, $content['code']);
        $this->assertSame('Value is missing for the property "enabled".', $content['message']);
    }

    public function testEnableFilterWithANonBooleanValue()
    {
        $client = $this->createAuthenticatedClient();

        $filters = [
            'enabled' => [['operator' => '=', 'value' => 'non_boolean']]
        ];
        $searchString = urlencode(json_encode($filters));
        $filterLocaleUrl = sprintf('api/rest/v1/locales?search=%s', $searchString);
        $client->request('GET', $filterLocaleUrl);

        $response = $client->getResponse();
        $this->assertSame(Response::HTTP_UNPROCESSABLE_ENTITY, $response->getStatusCode());
        $content = json_decode($response->getContent(), true);
        $this->assertCount(2, $content, 'response contains 2 items');
        $this->assertSame(Response::HTTP_UNPROCESSABLE_ENTITY, $content['code']);
        $this->assertSame('Filter "enabled" with operator "=" expects a boolean value', $content['message']);
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
