<?php

namespace AkeneoTest\Pim\Structure\EndToEnd\FamilyVariant\ExternalApi;

use Akeneo\Tool\Bundle\ApiBundle\Stream\StreamResourceResponse;
use Akeneo\Tool\Bundle\ApiBundle\tests\integration\ApiTestCase;
use Symfony\Component\HttpFoundation\Response;

class FamilyVariantAuthorizationEndToEnd extends ApiTestCase
{
    public function testOverallAccessDenied()
    {
        $client = $this->createAuthenticatedClient([], [], null, null, 'kevin', 'kevin');

        $client->request('GET', '/api/rest/v1/families/familyA/variants');

        $expectedResponse = <<<JSON
{
    "code": 403,
    "message": "You are not allowed to access the web API."
}
JSON;

        $response = $client->getResponse();
        $this->assertSame(Response::HTTP_FORBIDDEN, $response->getStatusCode());
        $this->assertJsonStringEqualsJsonString($expectedResponse, $response->getContent());
    }

    public function testAccessGrantedForListingFamilyVariants()
    {
        $client = $this->createAuthenticatedClient();

        $client->request('GET', '/api/rest/v1/families/familyA/variants');

        $response = $client->getResponse();
        $this->assertSame(Response::HTTP_OK, $response->getStatusCode());
    }

    public function testAccessDeniedForListingFamilyVariants()
    {
        $client = $this->createAuthenticatedClient([], [], null, null, 'julia', 'julia');

        $client->request('GET', '/api/rest/v1/families/familyA/variants');

        $expectedResponse = <<<JSON
{
    "code": 403,
    "message": "Access forbidden. You are not allowed to list family variants."
}
JSON;

        $response = $client->getResponse();
        $this->assertSame(Response::HTTP_FORBIDDEN, $response->getStatusCode());
        $this->assertJsonStringEqualsJsonString($expectedResponse, $response->getContent());
    }

    public function testAccessGrantedForGettingAFamilyVariant()
    {
        $client = $this->createAuthenticatedClient();

        $client->request('GET', '/api/rest/v1/families/familyA/variants/familyVariantA1');

        $response = $client->getResponse();
        $this->assertSame(Response::HTTP_OK, $response->getStatusCode());
    }

    public function testAccessDeniedForGettingAFamilyVariant()
    {
        $client = $this->createAuthenticatedClient([], [], null, null, 'julia', 'julia');

        $client->request('GET', '/api/rest/v1/families/familyA/variants/familyVariantA1');

        $expectedResponse = <<<JSON
{
    "code": 403,
    "message": "Access forbidden. You are not allowed to list family variants."
}
JSON;

        $response = $client->getResponse();
        $this->assertSame(Response::HTTP_FORBIDDEN, $response->getStatusCode());
        $this->assertJsonStringEqualsJsonString($expectedResponse, $response->getContent());
    }

    public function testAccessGrantedForCreatingAFamilyVariant()
    {
        $data = <<<JSON
{
    "code": "new_family_variant",
    "variant_attribute_sets": [
        {
            "level": 1,
            "axes": ["a_ref_data_simple_select"],
            "attributes": ["a_ref_data_simple_select"]
        },
        {
            "level": 2,
            "axes": ["a_yes_no"],
            "attributes": ["a_yes_no"]
        }
    ],
    "labels": {
        "en_US": "English label"
    }
}
JSON;

        $client = $this->createAuthenticatedClient();

        $client->request('POST', 'api/rest/v1/families/familyA/variants', [], [], [], $data);

        $response = $client->getResponse();
        $this->assertSame(Response::HTTP_CREATED, $response->getStatusCode());
    }

    public function testAccessDeniedForCreatingAFamilyVariant()
    {
        $data = <<<JSON
{
    "code": "new_family_variant",
    "variant_attribute_sets": [
        {
            "level": 1,
            "axes": ["a_ref_data_simple_select"],
            "attributes": ["a_ref_data_simple_select"]
        },
        {
            "level": 2,
            "axes": ["a_yes_no"],
            "attributes": ["a_yes_no"]
        }
    ],
    "labels": {
        "en_US": "English label"
    }
}
JSON;

        $client = $this->createAuthenticatedClient([], [], null, null, 'julia', 'julia');

        $client->request('POST', 'api/rest/v1/families/familyA/variants', [], [], [], $data);

        $expectedResponse = <<<JSON
{
    "code": 403,
    "message": "Access forbidden. You are not allowed to create or update family variants."
}
JSON;

        $response = $client->getResponse();
        $this->assertSame(Response::HTTP_FORBIDDEN, $response->getStatusCode());
        $this->assertJsonStringEqualsJsonString($expectedResponse, $response->getContent());
    }

    public function testAccessGrantedWhenUpdatingAFamilyVariant()
    {
        $data = <<<JSON
{
    "code": "familyVariantA1"
}
JSON;

        $client = $this->createAuthenticatedClient();

        $client->request('PATCH', 'api/rest/v1/families/familyA/variants/familyVariantA1', [], [], [], $data);

        $response = $client->getResponse();
        $this->assertSame(Response::HTTP_NO_CONTENT, $response->getStatusCode());
    }

    public function testAccessDeniedWhenUpdatingAFamilyVariant()
    {
        $data = <<<JSON
{
    "code": "familyVariantA1"
}
JSON;

        $client = $this->createAuthenticatedClient([], [], null, null, 'julia', 'julia');

        $client->request('PATCH', 'api/rest/v1/families/familyA/variants/familyVariantA1', [], [], [], $data);

        $expectedResponse = <<<JSON
{
    "code": 403,
    "message": "Access forbidden. You are not allowed to create or update family variants."
}
JSON;

        $response = $client->getResponse();
        $this->assertSame(Response::HTTP_FORBIDDEN, $response->getStatusCode());
        $this->assertJsonStringEqualsJsonString($expectedResponse, $response->getContent());
    }

    public function testAccessGrantedForPartialUpdatingAListOfFamilyVariant()
    {
        $client = $this->createAuthenticatedClient();
        $client->setServerParameter('CONTENT_TYPE', StreamResourceResponse::CONTENT_TYPE);

        $data = <<<JSON
{"code": "a_family_variant"}
JSON;

        ob_start(function() { return ''; });
        $client->request('PATCH', '/api/rest/v1/families/familyA/variants', [], [], [], $data);
        ob_end_flush();

        $response = $client->getResponse();
        $this->assertSame(Response::HTTP_OK, $response->getStatusCode());
    }

    public function testAccessDeniedForPartialUpdatingAListOfFamilyVariant()
    {
        $client = $this->createAuthenticatedClient([], [], null, null, 'julia', 'julia');
        $client->setServerParameter('CONTENT_TYPE', StreamResourceResponse::CONTENT_TYPE);

        $data = <<<JSON
{"code": "a_family_variant"}
JSON;

        $client->request('PATCH', '/api/rest/v1/families/familyA/variants', [], [], [], $data);

        $expectedResponse = <<<JSON
{
    "code": 403,
    "message": "Access forbidden. You are not allowed to create or update family variants."
}
JSON;

        $response = $client->getResponse();
        $this->assertSame(Response::HTTP_FORBIDDEN, $response->getStatusCode());
        $this->assertJsonStringEqualsJsonString($expectedResponse, $response->getContent());
    }

    /**
     * {@inheritdoc}
     */
    protected function getConfiguration()
    {
        return $this->catalog->useTechnicalCatalog();
    }
}
