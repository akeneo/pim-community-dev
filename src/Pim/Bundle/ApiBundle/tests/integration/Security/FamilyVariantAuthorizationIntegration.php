<?php

namespace Pim\Bundle\ApiBundle\tests\integration\Security;

use Akeneo\Test\Integration\Configuration;
use Pim\Bundle\ApiBundle\Stream\StreamResourceResponse;
use Pim\Bundle\ApiBundle\tests\integration\ApiTestCase;
use Symfony\Component\HttpFoundation\Response;

class FamilyVariantAuthorizationIntegration extends ApiTestCase
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

    public function testAccessDeniedForGettingAFamily()
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

    /**
     * {@inheritdoc}
     */
    protected function getConfiguration()
    {
        return $this->catalog->useTechnicalCatalog();
    }
}
