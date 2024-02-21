<?php

namespace AkeneoTest\Channel\EndToEnd\Channel\ExternalApi;

use Akeneo\Tool\Bundle\ApiBundle\tests\integration\ApiTestCase;
use Symfony\Component\HttpFoundation\Response;

class CreateChannelEndToEnd extends ApiTestCase
{
    public function testHttpHeadersInResponseWhenAChannelIsCreated()
    {
        $client = $this->createAuthenticatedClient();
        $data =
<<<JSON
    {
        "code":"new_channel",
        "category_tree": "master",
        "currencies": ["EUR"],
        "locales": ["fr_FR"]
    }
JSON;

        $client->request('POST', 'api/rest/v1/channels', [], [], [], $data);

        $response = $client->getResponse();

        $this->assertSame(Response::HTTP_CREATED, $response->getStatusCode());
        $this->assertArrayHasKey('location', $response->headers->all());
        $this->assertSame('http://localhost/api/rest/v1/channels/new_channel', $response->headers->get('location'));
        $this->assertSame('', $response->getContent());
    }

    public function testStandardFormatWhenAChannelIsCreatedButIncomplete()
    {
        $client = $this->createAuthenticatedClient();
        $data =
<<<JSON
    {
        "code":"nice_channel",
        "category_tree": "master",
        "currencies": ["EUR"],
        "locales": ["fr_FR"]
    }
JSON;

        $client->request('POST', 'api/rest/v1/channels', [], [], [], $data);

        $channel = $this->get('pim_catalog.repository.channel')->findOneByIdentifier('nice_channel');

        $channelStandard = [
            'code'             => 'nice_channel',
            'currencies'       => ['EUR'],
            'locales'          => ['fr_FR'],
            'category_tree'    => 'master',
            'conversion_units' => [],
            'labels'           => [],
        ];

        $normalizer = $this->get('pim_catalog.normalizer.standard.channel');

        $response = $client->getResponse();

        $this->assertSame(Response::HTTP_CREATED, $response->getStatusCode());
        $this->assertSame($channelStandard, $normalizer->normalize($channel));
    }

    public function testCompleteChannelCreation()
    {
        $client = $this->createAuthenticatedClient();
        $data =
<<<JSON
    {
        "code":"international_channel",
        "category_tree": "master",
        "currencies": ["EUR", "USD"],
        "locales": ["fr_FR", "en_US"],
        "labels":{
            "en_US": "International channel",
            "fr_FR": "Canal international"
        },
        "conversion_units":{
            "a_metric_without_decimal": "METER"
        }
    }
JSON;
        $client->request('POST', 'api/rest/v1/channels', [], [], [], $data);

        $channel = $this->get('pim_catalog.repository.channel')->findOneByIdentifier('international_channel');

        $channelStandard = [
            'code'             => 'international_channel',
            'currencies'       => ['EUR', 'USD'],
            'locales'          => ['fr_FR', 'en_US'],
            'category_tree'    => 'master',
            'conversion_units' => [
                'a_metric_without_decimal' => 'METER',
            ],
            'labels'           => [
                'en_US' => 'International channel',
                'fr_FR' => 'Canal international',
            ],
        ];

        $normalizer = $this->get('pim_catalog.normalizer.standard.channel');

        $response = $client->getResponse();

        $this->assertSame(Response::HTTP_CREATED, $response->getStatusCode());
        $this->assertSame($channelStandard, $normalizer->normalize($channel));
    }

    public function testChannelCreationWithEmptyLabels()
    {
        $client = $this->createAuthenticatedClient();
        $data =
<<<JSON
    {
        "code":"empty_label_channel",
        "category_tree": "master",
        "currencies": ["EUR"],
        "locales": ["fr_FR"],
        "labels":{
            "en_US": null,
            "fr_FR": ""
        }
    }
JSON;
        $client->request('POST', 'api/rest/v1/channels', [], [], [], $data);

        $channel = $this->get('pim_catalog.repository.channel')->findOneByIdentifier('empty_label_channel');

        $channelStandard = [
            'code'             => 'empty_label_channel',
            'currencies'       => ['EUR'],
            'locales'          => ['fr_FR'],
            'category_tree'    => 'master',
            'conversion_units' => [],
            'labels'           => [],
        ];

        $normalizer = $this->get('pim_catalog.normalizer.standard.channel');

        $response = $client->getResponse();

        $this->assertSame(Response::HTTP_CREATED, $response->getStatusCode());
        $this->assertSame($channelStandard, $normalizer->normalize($channel));
    }

    public function testResponseWhenContentIsEmpty()
    {
        $client = $this->createAuthenticatedClient();

        $client->request('POST', 'api/rest/v1/channels', [], [], [], '');

        $expectedContent =
<<<JSON
{
	"code": 400,
	"message": "Invalid json message received"
}
JSON;

        $response = $client->getResponse();
        $this->assertSame(Response::HTTP_BAD_REQUEST, $response->getStatusCode());
        $this->assertJsonStringEqualsJsonString($expectedContent, $response->getContent());
    }

    public function testResponseWhenContentIsNotValid()
    {
        $client = $this->createAuthenticatedClient();

        $client->request('POST', 'api/rest/v1/channels', [], [], [], '{');

        $expectedContent =
<<<JSON
{
	"code": 400,
	"message": "Invalid json message received"
}
JSON;

        $response = $client->getResponse();
        $this->assertSame(Response::HTTP_BAD_REQUEST, $response->getStatusCode());
        $this->assertJsonStringEqualsJsonString($expectedContent, $response->getContent());
    }

    public function testResponseWhenChannelCodeAlreadyExists()
    {
        $client = $this->createAuthenticatedClient();

        $data =
<<<JSON
    {
        "code":"ecommerce",
        "category_tree": "master",
        "currencies": ["EUR"],
        "locales": ["fr_FR"]
    }
JSON;

        $expectedContent =
<<<JSON
{
	"code": 422,
	"message": "Validation failed.",
	"errors": [{
	    "property": "code",
	    "message": "This value is already used."
	}]
}
JSON;
        $client->request('POST', 'api/rest/v1/channels', [], [], [], $data);

        $response = $client->getResponse();
        $this->assertSame(Response::HTTP_UNPROCESSABLE_ENTITY, $response->getStatusCode());
        $this->assertJsonStringEqualsJsonString($expectedContent, $response->getContent());
    }

    public function testResponseWhenAPropertyIsNotExpected()
    {
        $client = $this->createAuthenticatedClient();

        $data =
<<<JSON
    {
        "code":"new_ecommerce",
        "category_tree": "master",
        "currencies": ["EUR"],
        "locales": ["fr_FR"],
        "extra_property": ""
    }
JSON;

        $expectedContent =
<<<JSON
{
	"code": 422,
	"message": "Property \"extra_property\" does not exist. Check the expected format on the API documentation.",
	"_links": {
	    "documentation": {
	        "href": "http://api.akeneo.com/api-reference.html#post_channels"
	    }
	}
}
JSON;
        $client->request('POST', 'api/rest/v1/channels', [], [], [], $data);

        $response = $client->getResponse();
        $this->assertSame(Response::HTTP_UNPROCESSABLE_ENTITY, $response->getStatusCode());
        $this->assertJsonStringEqualsJsonString($expectedContent, $response->getContent());
    }

    public function testResponseWhenChannelCategoryTreeIsMissing()
    {
        $client = $this->createAuthenticatedClient();

        $data =
<<<JSON
    {
        "code":"ecommerce_ch",
        "currencies": ["EUR"],
        "locales": ["fr_FR"]
    }
JSON;

        $expectedContent =
            <<<JSON
{
	"code": 422,
	"message": "Validation failed.",
	"errors": [{
	    "property": "category_tree",
	    "message": "This value should not be blank."
	}]
}
JSON;
        $client->request('POST', 'api/rest/v1/channels', [], [], [], $data);

        $response = $client->getResponse();
        $this->assertSame(Response::HTTP_UNPROCESSABLE_ENTITY, $response->getStatusCode());
        $this->assertJsonStringEqualsJsonString($expectedContent, $response->getContent());
    }

    public function testChannelCreateWithNullValueForConversionUnits()
    {
        $client = $this->createAuthenticatedClient();
        $data =
<<<JSON
    {
        "code":"nice_channel",
        "category_tree": "master",
        "currencies": ["EUR"],
        "locales": ["fr_FR"],
        "conversion_units": {
            "a_metric_without_decimal": null
        }
    }
JSON;

        $client->request('POST', 'api/rest/v1/channels', [], [], [], $data);

        $channel = $this->get('pim_catalog.repository.channel')->findOneByIdentifier('nice_channel');

        $channelStandard = [
            'code'             => 'nice_channel',
            'currencies'       => ['EUR'],
            'locales'          => ['fr_FR'],
            'category_tree'    => 'master',
            'conversion_units' => [],
            'labels'           => [],
        ];

        $normalizer = $this->get('pim_catalog.normalizer.standard.channel');

        $response = $client->getResponse();

        $this->assertSame(Response::HTTP_CREATED, $response->getStatusCode());
        $this->assertSame($channelStandard, $normalizer->normalize($channel));
    }

    /**
     * {@inheritdoc}
     */
    protected function getConfiguration()
    {
        return $this->catalog->useTechnicalCatalog();
    }
}
