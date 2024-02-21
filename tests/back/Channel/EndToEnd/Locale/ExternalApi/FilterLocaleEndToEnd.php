<?php

namespace AkeneoTest\Channel\EndToEnd\Locale\ExternalApi;

use Akeneo\Tool\Bundle\ApiBundle\tests\integration\ApiTestCase;
use Symfony\Component\HttpFoundation\Response;

class FilterLocaleEndToEnd extends ApiTestCase
{
    public function testFilterActivatedLocales()
    {
        $client = $this->createAuthenticatedClient();

        $searchString = $this->encodeStringWithSymfonyUrlGeneratorCompatibility('{"enabled":[{"operator":"=","value":true}]}');
        $filterLocaleUrl = sprintf('api/rest/v1/locales?search=%s', $searchString);
        $client->request('GET', $filterLocaleUrl);

        $standardLocales = [
            '_links'       => [
                'self'  => [
                    'href' => sprintf('http://localhost/api/rest/v1/locales?page=1&limit=10&with_count=false&search=%s', $searchString)
                ],
                'first' => [
                    'href' => sprintf('http://localhost/api/rest/v1/locales?page=1&limit=10&with_count=false&search=%s', $searchString)
                ]
            ],
            'current_page' => 1,
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

        $searchString = $this->encodeStringWithSymfonyUrlGeneratorCompatibility('{"enabled":[{"operator":"=","value":false}]}');
        $filterLocaleUrl = sprintf('api/rest/v1/locales?search=%s', $searchString);
        $client->request('GET', $filterLocaleUrl);

        $standardLocales = [
            '_links'       => [
                'self'  => [
                    'href' => sprintf('http://localhost/api/rest/v1/locales?page=1&limit=10&with_count=false&search=%s', $searchString)
                ],
                'first' => [
                    'href' => sprintf('http://localhost/api/rest/v1/locales?page=1&limit=10&with_count=false&search=%s', $searchString)
                ],
                'next'  => [
                    'href' => sprintf('http://localhost/api/rest/v1/locales?page=2&limit=10&with_count=false&search=%s', $searchString)
                ],
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

    public function testFilterOnUnauthorizedField()
    {
        $client = $this->createAuthenticatedClient();

        $searchString = '{"unauthorized":[{"operator":"=","value":true}]}';
        $filterLocaleUrl = sprintf('api/rest/v1/locales?search=%s', $searchString);
        $client->request('GET', $filterLocaleUrl);

        $response = $client->getResponse();
        $this->assertSame(Response::HTTP_UNPROCESSABLE_ENTITY, $response->getStatusCode());
        $content = json_decode($response->getContent(), true);
        $this->assertCount(2, $content, 'response contains 2 items');
        $this->assertSame(Response::HTTP_UNPROCESSABLE_ENTITY, $content['code']);
        $this->assertSame(
            'Filter on property "unauthorized" is not supported or does not support operator "=".',
            $content['message']
        );
    }

    public function testFilterOnInvalidJson()
    {
        $client = $this->createAuthenticatedClient();

        $searchString = '{"enabled":[{"operator":"=","value\': "]}';
        $filterLocaleUrl = sprintf('api/rest/v1/locales?search=%s', $searchString);
        $client->request('GET', $filterLocaleUrl);

        $response = $client->getResponse();
        $this->assertSame(Response::HTTP_BAD_REQUEST, $response->getStatusCode());
        $content = json_decode($response->getContent(), true);
        $this->assertCount(2, $content, 'response contains 2 items');
        $this->assertSame(Response::HTTP_BAD_REQUEST, $content['code']);
        $this->assertSame('Search query parameter should be valid JSON.', $content['message']);
    }

    public function testFilterOnInvalidJsonForFilterPart()
    {
        $client = $this->createAuthenticatedClient();

        $searchString = '{"enabled":{"operator":"=","value":false}}';
        $filterLocaleUrl = sprintf('api/rest/v1/locales?search=%s', $searchString);
        $client->request('GET', $filterLocaleUrl);

        $response = $client->getResponse();
        $this->assertSame(Response::HTTP_UNPROCESSABLE_ENTITY, $response->getStatusCode());
        $content = json_decode($response->getContent(), true);
        $this->assertCount(2, $content, 'response contains 2 items');
        $this->assertSame(Response::HTTP_UNPROCESSABLE_ENTITY, $content['code']);
        $expectedErrorMessage = sprintf(
            'Structure of filter "enabled" should respect this structure: %s',
            '{"enabled":[{"operator": "my_operator", "value": "my_value"}]}'
        );
        $this->assertSame($expectedErrorMessage, $content['message']);
    }

    public function testFilterMissingOperatorAndValueFields()
    {
        $client = $this->createAuthenticatedClient();

        $searchString = '{"enabled":[]}';
        $filterLocaleUrl = sprintf('api/rest/v1/locales?search=%s', $searchString);
        $client->request('GET', $filterLocaleUrl);

        $response = $client->getResponse();
        $this->assertSame(Response::HTTP_UNPROCESSABLE_ENTITY, $response->getStatusCode());
        $content = json_decode($response->getContent(), true);
        $this->assertCount(2, $content, 'response contains 2 items');
        $this->assertSame(Response::HTTP_UNPROCESSABLE_ENTITY, $content['code']);
        $expectedErrorMessage = sprintf(
            'Structure of filter "enabled" should respect this structure: %s',
            '{"enabled":[{"operator": "my_operator", "value": "my_value"}]}'
        );
        $this->assertSame($expectedErrorMessage, $content['message']);
    }

    public function testFilterMissingOperatorField()
    {
        $client = $this->createAuthenticatedClient();

        $searchString = '{"enabled":[{"value":true}]}';
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

        $searchString = '{"enabled":[{"operator":"="}]}';
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

        $searchString = '{"enabled":[{"operator":"=","value":"non_boolean"}]}';
        $filterLocaleUrl = sprintf('api/rest/v1/locales?search=%s', $searchString);
        $client->request('GET', $filterLocaleUrl);

        $response = $client->getResponse();
        $this->assertSame(Response::HTTP_UNPROCESSABLE_ENTITY, $response->getStatusCode());
        $content = json_decode($response->getContent(), true);
        $this->assertCount(2, $content, 'response contains 2 items');
        $this->assertSame(Response::HTTP_UNPROCESSABLE_ENTITY, $content['code']);
        $this->assertSame('Filter "enabled" with operator "=" expects a boolean value.', $content['message']);
    }

    /**
     * {@inheritdoc}
     */
    protected function getConfiguration()
    {
        return $this->catalog->useTechnicalCatalog();
    }
}
