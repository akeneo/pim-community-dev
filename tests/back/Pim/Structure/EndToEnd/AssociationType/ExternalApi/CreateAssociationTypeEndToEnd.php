<?php

namespace AkeneoTest\Pim\Structure\EndToEnd\AssociationType\ExternalApi;

use Akeneo\Tool\Bundle\ApiBundle\tests\integration\ApiTestCase;
use Symfony\Component\HttpFoundation\Response;

class CreateAssociationTypeEndToEnd extends ApiTestCase
{
    public function testHttpHeadersInResponseWhenAnAssociationTypeIsCreated()
    {
        $client = $this->createAuthenticatedClient();
        $data =
<<<JSON
    {
        "code":"NEW_SELL"
    }
JSON;

        $client->request('POST', 'api/rest/v1/association-types', [], [], [], $data);

        $response = $client->getResponse();

        $this->assertSame(Response::HTTP_CREATED, $response->getStatusCode());
        $this->assertArrayHasKey('location', $response->headers->all());
        $this->assertSame('http://localhost/api/rest/v1/association-types/NEW_SELL', $response->headers->get('location'));
        $this->assertSame('', $response->getContent());
    }

    public function testStandardFormatWhenAnAssociationTypeIsCreatedButIncomplete()
    {
        $client = $this->createAuthenticatedClient();
        $data =
<<<JSON
    {
        "code":"NEW_SELL"
    }
JSON;

        $client->request('POST', 'api/rest/v1/association-types', [], [], [], $data);

        $associationType = $this->get('pim_catalog.repository.association_type')->findOneByIdentifier('NEW_SELL');
        $associationTypeStandard = [
            'code'       => 'NEW_SELL',
            'labels'     => [],
        ];
        $normalizer = $this->get('pim_catalog.normalizer.standard.association_type');

        $response = $client->getResponse();

        $this->assertSame(Response::HTTP_CREATED, $response->getStatusCode());
        $this->assertSame($associationTypeStandard, $normalizer->normalize($associationType));
    }

    public function testCompleteAssociationTypeCreation()
    {
        $client = $this->createAuthenticatedClient();
        $data =
<<<JSON
    {
        "code":"NEW_SELL",
        "labels": {
            "en_US": "New sell",
            "fr_FR": "Nouvelle vente"
        }
    }
JSON;

        $client->request('POST', 'api/rest/v1/association-types', [], [], [], $data);

        $associationType = $this->get('pim_catalog.repository.association_type')->findOneByIdentifier('NEW_SELL');

        $associationTypeStandard = [
            'code'       => 'NEW_SELL',
            'labels'     => [
                'en_US' => 'New sell',
                'fr_FR' => 'Nouvelle vente',
            ],
        ];
        $normalizer = $this->get('pim_catalog.normalizer.standard.association_type');

        $response = $client->getResponse();

        $this->assertSame(Response::HTTP_CREATED, $response->getStatusCode());
        $this->assertSame($associationTypeStandard, $normalizer->normalize($associationType));
    }

    public function testAssociationTypeCreationWithEmptyLabels()
    {
        $client = $this->createAuthenticatedClient();
        $data =
<<<JSON
    {
        "code":"NEW_SELL",
        "labels": {
            "en_US": null,
            "fr_FR": ""
        }
    }
JSON;

        $client->request('POST', 'api/rest/v1/association-types', [], [], [], $data);

        $associationType = $this->get('pim_catalog.repository.association_type')->findOneByIdentifier('NEW_SELL');

        $associationTypeStandard = [
            'code'       => 'NEW_SELL',
            'labels'     => [],
        ];
        $normalizer = $this->get('pim_catalog.normalizer.standard.association_type');

        $response = $client->getResponse();

        $this->assertSame(Response::HTTP_CREATED, $response->getStatusCode());
        $this->assertSame($associationTypeStandard, $normalizer->normalize($associationType));
    }

    public function testResponseWhenContentIsEmpty()
    {
        $client = $this->createAuthenticatedClient();

        $client->request('POST', 'api/rest/v1/association-types', [], [], [], '');

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

        $client->request('POST', 'api/rest/v1/association-types', [], [], [], '{');

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

    public function testResponseWhenValidationFailed()
    {
        $client = $this->createAuthenticatedClient();

        $data =
<<<JSON
    {
        "code":"X_SELL"
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

        $client->request('POST', 'api/rest/v1/association-types', [], [], [], $data);

        $response = $client->getResponse();
        $this->assertSame(Response::HTTP_UNPROCESSABLE_ENTITY, $response->getStatusCode());
        $this->assertJsonStringEqualsJsonString($expectedContent, $response->getContent());
    }

    public function testResponseWhenAnExceptionThrowInUpdater()
    {
        $client = $this->createAuthenticatedClient();

        $data =
<<<JSON
    {
        "code":[]
    }
JSON;

        $expectedContent =
<<<JSON
{
	"code": 422,
	"message": "Property \"code\" expects a scalar as data, \"array\" given. Check the expected format on the API documentation.",
	"_links": {
	    "documentation": {
	        "href": "http://api.akeneo.com/api-reference.html#post_association_types"
	    }
	}
}
JSON;

        $client->request('POST', 'api/rest/v1/association-types', [], [], [], $data);

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
