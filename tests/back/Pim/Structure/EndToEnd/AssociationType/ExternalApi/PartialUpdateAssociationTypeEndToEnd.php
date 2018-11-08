<?php

namespace AkeneoTest\Pim\Structure\EndToEnd\AssociationType\ExternalApi;

use Akeneo\Tool\Bundle\ApiBundle\tests\integration\ApiTestCase;
use Symfony\Component\HttpFoundation\Response;

class PartialUpdateAssociationTypeEndToEnd extends ApiTestCase
{
    public function testHttpHeadersInResponseWhenAnAssociationTypeIsUpdated()
    {
        $client = $this->createAuthenticatedClient();
        $data =
<<<JSON
    {
        "code": "X_SELL"
    }
JSON;
        $client->request('PATCH', '/api/rest/v1/association-types/X_SELL', [], [], [], $data);
        $response = $client->getResponse();
        $this->assertSame(Response::HTTP_NO_CONTENT, $response->getStatusCode());
        $this->assertSame('http://localhost/api/rest/v1/association-types/X_SELL', $response->headers->get('location'));
        $this->assertSame('', $response->getContent());
    }


    public function testHttpHeadersInResponseWhenAnAssociationTypeCreated()
    {
        $client = $this->createAuthenticatedClient();
        $data =
<<<JSON
    {
        "code":"NEW_SELL"
    }
JSON;
        $client->request('PATCH', '/api/rest/v1/association-types/NEW_SELL', [], [], [], $data);
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
        "code":"YOLO_SELL"
    }
JSON;
        $client->request('PATCH', '/api/rest/v1/association-types/YOLO_SELL', [], [], [], $data);
        $associationType = $this->get('pim_catalog.repository.association_type')->findOneByIdentifier('YOLO_SELL');
        $associationTypeStandard = [
            'code'             => 'YOLO_SELL',
            'labels'           => [],
        ];
        $normalizer = $this->get('pim_catalog.normalizer.standard.association_type');
        $response = $client->getResponse();
        $this->assertSame(Response::HTTP_CREATED, $response->getStatusCode());
        $this->assertSame($associationTypeStandard, $normalizer->normalize($associationType));
    }

    public function testCompleteAssociationTypeCreationWithoutCodeProvided()
    {
        $client = $this->createAuthenticatedClient();
        $data =
<<<JSON
    {
        "labels": {
            "en_US": "Gift sell",
            "fr_FR": "Vente cadeau"
        }
    }
JSON;
        $client->request('PATCH', '/api/rest/v1/association-types/GIFT_SELL', [], [], [], $data);
        $associationType = $this->get('pim_catalog.repository.association_type')->findOneByIdentifier('GIFT_SELL');
        $associationTypeStandard = [
            'code'   => 'GIFT_SELL',
            'labels' => [
                'en_US' => 'Gift sell',
                'fr_FR' => 'Vente cadeau',
            ],
        ];
        $normalizer = $this->get('pim_catalog.normalizer.standard.association_type');
        $response = $client->getResponse();
        $this->assertSame(Response::HTTP_CREATED, $response->getStatusCode());
        $this->assertSame($associationTypeStandard, $normalizer->normalize($associationType));
    }

    public function testAssociationTypePartialUpdateWithAnEmptyContent()
    {
        $client = $this->createAuthenticatedClient();
        $data = '{}';
        $client->request('PATCH', '/api/rest/v1/association-types/X_SELL', [], [], [], $data);
        $associationType = $this->get('pim_catalog.repository.association_type')->findOneByIdentifier('X_SELL');
        $associationTypeStandard = [
            'code'             => 'X_SELL',
            'labels'           => [
                'en_US' => 'Cross sell',
                'fr_FR' => 'Vente croisée',
            ],
        ];
        $normalizer = $this->get('pim_catalog.normalizer.standard.association_type');
        $response = $client->getResponse();
        $this->assertSame(Response::HTTP_NO_CONTENT, $response->getStatusCode());
        $this->assertSame($associationTypeStandard, $normalizer->normalize($associationType));
    }

    public function testAssociationTypePartialUpdateWithEmptyLabels()
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
        $client->request('PATCH', '/api/rest/v1/association-types/X_SELL', [], [], [], $data);
        $associationType = $this->get('pim_catalog.repository.association_type')->findOneByIdentifier('X_SELL');
        $associationTypeStandard = [
            'code'             => 'X_SELL',
            'labels'           => [
                'fr_FR' => 'Vente croisée',
            ],
        ];
        $normalizer = $this->get('pim_catalog.normalizer.standard.association_type');
        $response = $client->getResponse();
        $this->assertSame(Response::HTTP_NO_CONTENT, $response->getStatusCode());
        $this->assertSame($associationTypeStandard, $normalizer->normalize($associationType));
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
        $client->request('PATCH', '/api/rest/v1/association-types/X_SELL', [], [], [], $data);
        $response = $client->getResponse();
        $this->assertSame(Response::HTTP_BAD_REQUEST, $response->getStatusCode());
        $this->assertJsonStringEqualsJsonString($expectedContent, $response->getContent());
    }

    public function testResponseWhenAnAssociationTypeIsCreatedWithInconsistentCodes()
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
        $client->request('PATCH', '/api/rest/v1/association-types/inconsistent_code1', [], [], [], $data);
        $response = $client->getResponse();
        $this->assertSame(Response::HTTP_UNPROCESSABLE_ENTITY, $response->getStatusCode());
        $this->assertJsonStringEqualsJsonString($expectedContent, $response->getContent());
    }

    public function testResponseWhenAssociationTypePartialUpdateValidationFailed()
    {
        $client = $this->createAuthenticatedClient();
        $data =
<<<JSON
    {
        "code": "X_SELL",
        "labels": {
            "yo_LO": "YOLO"
        }
    }
JSON;
        $expectedContent =
<<<JSON
{
    "code":422,
    "message": "Validation failed.",
    "errors": [
        {
            "property": "labels",
            "message": "The locale \"yo_LO\" does not exist."
        }
    ]
}
JSON;
        $client->request('PATCH', '/api/rest/v1/association-types/X_SELL', [], [], [], $data);
        $response = $client->getResponse();
        $this->assertSame(Response::HTTP_UNPROCESSABLE_ENTITY, $response->getStatusCode());
        $this->assertJsonStringEqualsJsonString($expectedContent, $response->getContent());
    }

    public function testResponseWhenAssociationTypePartialUpdateWhenAPropertyIsNotExpected()
    {
        $client = $this->createAuthenticatedClient();
        $data =
<<<JSON
    {
        "code": "X_SELL",
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
            "href": "http://api.akeneo.com/api-reference.html#patch_association_types__code_"
        }
    }
}
JSON;
        $client->request('PATCH', '/api/rest/v1/association-types/X_SELL', [], [], [], $data);
        $response = $client->getResponse();
        $this->assertSame(Response::HTTP_UNPROCESSABLE_ENTITY, $response->getStatusCode());
        $this->assertJsonStringEqualsJsonString($expectedContent, $response->getContent());
    }

    public function testResponseWhenAssociationTypeCreationValidationFailed()
    {
        $client = $this->createAuthenticatedClient();
        $data =
<<<JSON
    {
        "code":"::EASY_SELL"
    }
JSON;
        $expectedContent =
<<<JSON
{
    "code":422,
    "message": "Validation failed.",
    "errors": [
        {
            "property": "code",
            "message": "Association type code may contain only letters, numbers and underscores"
        }
    ]
}
JSON;
        $client->request('PATCH', '/api/rest/v1/association-types/::EASY_SELL', [], [], [], $data);
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
