<?php

namespace AkeneoTest\Channel\EndToEnd\Channel\ExternalApi;

use Akeneo\Tool\Bundle\ApiBundle\tests\integration\ApiTestCase;
use Symfony\Component\HttpFoundation\Response;

class PartialUpdateChannelEndToEnd extends ApiTestCase
{
    public function testHttpHeadersInResponseWhenAChannelIsUpdated()
    {
        $client = $this->createAuthenticatedClient();

        $data =
<<<JSON
    {
        "code": "ecommerce"
    }
JSON;

        $client->request('PATCH', '/api/rest/v1/channels/ecommerce', [], [], [], $data);

        $response = $client->getResponse();

        $this->assertSame(Response::HTTP_NO_CONTENT, $response->getStatusCode());
        $this->assertSame('http://localhost/api/rest/v1/channels/ecommerce', $response->headers->get('location'));
        $this->assertSame('', $response->getContent());
    }

    public function testHttpHeadersInResponseWhenAChannelCreated()
    {
        $client = $this->createAuthenticatedClient();
        $data =
<<<JSON
    {
        "code":"mobile",
        "category_tree": "master_china",
        "currencies": ["EUR"],
        "locales": ["fr_FR"]
    }
JSON;

        $client->request('PATCH', '/api/rest/v1/channels/mobile', [], [], [], $data);

        $response = $client->getResponse();
        $this->assertSame(Response::HTTP_CREATED, $response->getStatusCode());
        $this->assertArrayHasKey('location', $response->headers->all());
        $this->assertSame('http://localhost/api/rest/v1/channels/mobile', $response->headers->get('location'));
        $this->assertSame('', $response->getContent());
    }

    public function testStandardFormatWhenAChannelIsCreatedButIncomplete()
    {
        $client = $this->createAuthenticatedClient();
        $data =
<<<JSON
    {
        "code":"mobile",
        "category_tree": "master_china",
        "currencies": ["EUR"],
        "locales": ["fr_FR"]
    }
JSON;

        $client->request('PATCH', '/api/rest/v1/channels/mobile', [], [], [], $data);
        $channel = $this->get('pim_catalog.repository.channel')->findOneByIdentifier('mobile');
        $channelStandard = [
            'code'             => 'mobile',
            'currencies'       => ['EUR'],
            'locales'          => ['fr_FR'],
            'category_tree'    => 'master_china',
            'conversion_units' => [],
            'labels'           => [],
        ];
        $normalizer = $this->get('pim_catalog.normalizer.standard.channel');
        $response = $client->getResponse();
        $this->assertSame(Response::HTTP_CREATED, $response->getStatusCode());
        $this->assertSame($channelStandard, $normalizer->normalize($channel));
    }

    public function testCompleteChannelCreationWithoutCodeProvided()
    {
        $client = $this->createAuthenticatedClient();
        $data =
<<<JSON
    {
        "category_tree": "master_china",
        "currencies": ["EUR"],
        "locales": ["fr_FR"]
    }
JSON;

        $client->request('PATCH', '/api/rest/v1/channels/mobile', [], [], [], $data);
        $channel = $this->get('pim_catalog.repository.channel')->findOneByIdentifier('mobile');
        $channelStandard = [
            'code'             => 'mobile',
            'currencies'       => ['EUR'],
            'locales'          => ['fr_FR'],
            'category_tree'    => 'master_china',
            'conversion_units' => [],
            'labels'           => [],
        ];
        $normalizer = $this->get('pim_catalog.normalizer.standard.channel');
        $response = $client->getResponse();
        $this->assertSame(Response::HTTP_CREATED, $response->getStatusCode());
        $this->assertSame($channelStandard, $normalizer->normalize($channel));
    }

    public function testChannelPartialUpdateWithAnEmptyContent()
    {
        $client = $this->createAuthenticatedClient();

        $data = '{}';

        $client->request('PATCH', '/api/rest/v1/channels/ecommerce', [], [], [], $data);

        $channel = $this->get('pim_catalog.repository.channel')->findOneByIdentifier('ecommerce');
        $channelStandard = [
            'code'             => 'ecommerce',
            'currencies'       => ['USD', 'EUR'],
            'locales'          => ['en_US'],
            'category_tree'    => 'master',
            'conversion_units' => [
                'a_metric_without_decimal' => 'METER',
                'a_metric' => 'KILOWATT',
            ],
            'labels'           => [
                'en_US' => 'Ecommerce',
                'fr_FR' => 'Ecommerce',
            ],
        ];
        $normalizer = $this->get('pim_catalog.normalizer.standard.channel');
        $response = $client->getResponse();
        $this->assertSame(Response::HTTP_NO_CONTENT, $response->getStatusCode());
        $this->assertSame($channelStandard, $normalizer->normalize($channel));
    }

    public function testChannelPartialUpdateWithEmptyLabels()
    {
        $client = $this->createAuthenticatedClient();

        $data =
<<<JSON
{
    "labels": {
        "en_US": ""
    }
}
JSON;
        $client->request('PATCH', '/api/rest/v1/channels/ecommerce', [], [], [], $data);

        $channel = $this->get('pim_catalog.repository.channel')->findOneByIdentifier('ecommerce');
        $channelStandard = [
            'code'             => 'ecommerce',
            'currencies'       => ['USD', 'EUR'],
            'locales'          => ['en_US'],
            'category_tree'    => 'master',
            'conversion_units' => [
                'a_metric_without_decimal' => 'METER',
                'a_metric'                 => 'KILOWATT',
            ],
            'labels'           => [
                'fr_FR' => 'Ecommerce',
            ],
        ];
        $normalizer = $this->get('pim_catalog.normalizer.standard.channel');
        $response = $client->getResponse();
        $this->assertSame(Response::HTTP_NO_CONTENT, $response->getStatusCode());
        $this->assertSame($channelStandard, $normalizer->normalize($channel));
    }

    public function testChannelPartialUpdateWithEmptyConversionUnits()
    {
        $client = $this->createAuthenticatedClient();

        $data =
<<<JSON
{
    "conversion_units" : {
        "a_metric_without_decimal": null
    }
}
JSON;
        $client->request('PATCH', '/api/rest/v1/channels/ecommerce', [], [], [], $data);

        $channel = $this->get('pim_catalog.repository.channel')->findOneByIdentifier('ecommerce');
        $channelStandard = [
            'code'             => 'ecommerce',
            'currencies'       => ['USD', 'EUR'],
            'locales'          => ['en_US'],
            'category_tree'    => 'master',
            'conversion_units' => [
                'a_metric' => 'KILOWATT',
            ],
            'labels'           => [
                'en_US' => 'Ecommerce',
                'fr_FR' => 'Ecommerce',
            ],
        ];
        $normalizer = $this->get('pim_catalog.normalizer.standard.channel');
        $response = $client->getResponse();
        $this->assertSame(Response::HTTP_NO_CONTENT, $response->getStatusCode());
        $this->assertSame($channelStandard, $normalizer->normalize($channel));
    }

    public function testResponseWhenContentIsEmpty()
    {
        $client = $this->createAuthenticatedClient();

        $data = '';

        $expectedContent =
<<<JSON
{
    "code":400,
    "message": "Invalid json message received"
}
JSON;

        $client->request('PATCH', '/api/rest/v1/channels/ecommerce', [], [], [], $data);
        $response = $client->getResponse();
        $this->assertSame(Response::HTTP_BAD_REQUEST, $response->getStatusCode());
        $this->assertJsonStringEqualsJsonString($expectedContent, $response->getContent());
    }

    public function testResponseWhenAChannelIsCreatedWithInconsistentCodes()
    {
        $client = $this->createAuthenticatedClient();

        $data =
<<<JSON
    {
        "code": "inconsistent_code2"
    }
JSON;

        $expectedContent =
<<<JSON
{
    "code":422,
    "message": "The code \"inconsistent_code2\" provided in the request body must match the code \"inconsistent_code1\" provided in the url."
}
JSON;
        $client->request('PATCH', '/api/rest/v1/channels/inconsistent_code1', [], [], [], $data);

        $response = $client->getResponse();
        $this->assertSame(Response::HTTP_UNPROCESSABLE_ENTITY, $response->getStatusCode());
        $this->assertJsonStringEqualsJsonString($expectedContent, $response->getContent());
    }

    public function testResponseWhenChannelPartialUpdateValidationFailed()
    {
        $client = $this->createAuthenticatedClient();

        $data =
<<<JSON
    {
        "code": "ecommerce",
        "locales": {}
    }
JSON;

        $expectedContent =
<<<JSON
{
    "code":422,
    "message": "Validation failed.",
    "errors": [
        {
            "property": "locales",
            "message": "This collection should contain 1 element or more."
        }
    ]
}
JSON;
        $client->request('PATCH', '/api/rest/v1/channels/ecommerce', [], [], [], $data);

        $response = $client->getResponse();
        $this->assertSame(Response::HTTP_UNPROCESSABLE_ENTITY, $response->getStatusCode());
        $this->assertJsonStringEqualsJsonString($expectedContent, $response->getContent());
    }

    public function testResponseWhenCurrencyDoesNotExist()
    {
        $client = $this->createAuthenticatedClient();

        $data = '{ "currencies": ["ADP"] }';

        $expectedContent =
<<<JSON
{
    "code":422,
    "message": "Validation failed.",
    "errors": [
        {
            "property": "currencies",
            "message": "The currency \"ADP\" has to be activated."
        }
    ]
}
JSON;
        $client->request('PATCH', '/api/rest/v1/channels/ecommerce', [], [], [], $data);

        $response = $client->getResponse();
        $this->assertSame(Response::HTTP_UNPROCESSABLE_ENTITY, $response->getStatusCode());
        $this->assertJsonStringEqualsJsonString($expectedContent, $response->getContent());
    }

    public function testResponseWhenChannelPartialUpdateWhenAPropertyIsNotExpected()
    {
        $client = $this->createAuthenticatedClient();

        $data =
<<<JSON
    {
        "code": "ecommerce",
        "group": "technical"
    }
JSON;

        $expectedContent =
<<<JSON
{
    "code":422,
    "message": "Property \"group\" does not exist. Check the expected format on the API documentation.",
    "_links": {
        "documentation": {
            "href": "http://api.akeneo.com/api-reference.html#patch_channels__code_"
        }
    }
}
JSON;

        $client->request('PATCH', '/api/rest/v1/channels/ecommerce', [], [], [], $data);

        $response = $client->getResponse();
        $this->assertSame(Response::HTTP_UNPROCESSABLE_ENTITY, $response->getStatusCode());
        $this->assertJsonStringEqualsJsonString($expectedContent, $response->getContent());
    }

    public function testResponseWhenChannelCreationValidationFailed()
    {
        $client = $this->createAuthenticatedClient();
        $data =
<<<JSON
    {
        "code":"mobile",
        "currencies": ["EUR"],
        "locales": ["fr_FR"]
    }
JSON;

        $expectedContent =
<<<JSON
{
    "code":422,
    "message": "Validation failed.",
    "errors": [
        {
            "property": "category_tree",
            "message": "This value should not be blank."
        }
    ]
}
JSON;
        $client->request('PATCH', '/api/rest/v1/channels/mobile', [], [], [], $data);

        $response = $client->getResponse();
        $this->assertSame(Response::HTTP_UNPROCESSABLE_ENTITY, $response->getStatusCode());
        $this->assertJsonStringEqualsJsonString($expectedContent, $response->getContent());
    }

    /**
     * {@inheritdoc}
     */
    protected function getConfiguration()
    {
        return $this->catalog->useTechnicalCatalog();
    }
}
