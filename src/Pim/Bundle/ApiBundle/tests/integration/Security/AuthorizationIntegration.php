<?php

namespace Pim\Bundle\ApiBundle\tests\integration\Security;

use Akeneo\Test\Integration\Configuration;
use Pim\Bundle\ApiBundle\tests\integration\ApiTestCase;
use Symfony\Component\HttpFoundation\Response;

/**
 * @author    Yohan Blain <yohan.blain@akeneo.com>
 * @copyright 2017 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 */
class AuthorizationIntegration extends ApiTestCase
{
    public function testOverallAccessGranted()
    {
        $client = $this->createAuthenticatedClient();

        $client->request('GET', '/api/rest/v1/categories/master');

        $standardCategory = [
            'code'   => 'master',
            'parent' => null,
            'labels' => []
        ];

        $response = $client->getResponse();
        $this->assertSame(Response::HTTP_OK, $response->getStatusCode());
        $this->assertSame($standardCategory, json_decode($response->getContent(), true));
    }

    public function testOverallAccessDenied()
    {
        $client = $this->createAuthenticatedClient([], [], null, null, 'mary', 'mary');

        $client->request('GET', '/api/rest/v1/categories/master');

        $expectedResponse = [
            'code'    => 403,
            'message' => 'You are not allowed to access the web API.',
        ];

        $response = $client->getResponse();
        $this->assertSame(Response::HTTP_FORBIDDEN, $response->getStatusCode());
        $this->assertSame($expectedResponse, json_decode($response->getContent(), true));
    }

    public function testAccessGrantedForListingAttributes()
    {
        $client = $this->createAuthenticatedClient();

        $client->request('GET', '/api/rest/v1/attributes');

        $response = $client->getResponse();
        $this->assertSame(Response::HTTP_OK, $response->getStatusCode());
    }

    public function testAccessDeniedForListingAttributes()
    {
        $client = $this->createAuthenticatedClient([], [], null, null, 'julia', 'julia');

        $client->request('GET', '/api/rest/v1/attributes');

        $expectedResponse = [
            'code'    => 403,
            'message' => 'Access forbidden. You are not allowed to list attributes.',
        ];

        $response = $client->getResponse();
        $this->assertSame(Response::HTTP_FORBIDDEN, $response->getStatusCode());
        $this->assertSame($expectedResponse, json_decode($response->getContent(), true));
    }

    public function testAccessGrantedForEditingAttributes()
    {
        $client = $this->createAuthenticatedClient();

        $data =
<<<JSON
    {
        "code":"an_incomplete_text",
        "type":"pim_catalog_text",
        "group":"attributeGroupA"
    }
JSON;

        $client->request('POST', '/api/rest/v1/attributes', [], [], [], $data);

        $response = $client->getResponse();
        $this->assertSame(Response::HTTP_CREATED, $response->getStatusCode());
    }

    public function testAccessDeniedForEditingAttributes()
    {
        $client = $this->createAuthenticatedClient([], [], null, null, 'julia', 'julia');

        $data =
<<<JSON
    {
        "code":"an_incomplete_text",
        "type":"pim_catalog_text",
        "group":"attributeGroupA"
    }
JSON;

        $client->request('POST', '/api/rest/v1/attributes', [], [], [], $data);

        $expectedResponse = [
            'code'    => 403,
            'message' => 'Access forbidden. You are not allowed to create or update attributes.',
        ];

        $response = $client->getResponse();
        $this->assertSame(Response::HTTP_FORBIDDEN, $response->getStatusCode());
        $this->assertSame($expectedResponse, json_decode($response->getContent(), true));
    }

    public function testAccessGrantedForListingAttributeOptions()
    {
        $client = $this->createAuthenticatedClient();

        $client->request('GET', '/api/rest/v1/attributes/a_multi_select/options/optionA');

        $response = $client->getResponse();
        $this->assertSame(Response::HTTP_OK, $response->getStatusCode());
    }

    public function testAccessDeniedForListingAttributeOptions()
    {
        $client = $this->createAuthenticatedClient([], [], null, null, 'julia', 'julia');

        $client->request('GET', '/api/rest/v1/attributes/a_multi_select/options/optionA');

        $expectedResponse = [
            'code'    => 403,
            'message' => 'Access forbidden. You are not allowed to list attribute options.',
        ];

        $response = $client->getResponse();
        $this->assertSame(Response::HTTP_FORBIDDEN, $response->getStatusCode());
        $this->assertSame($expectedResponse, json_decode($response->getContent(), true));
    }

    public function testAccessGrantedForEditingAttributeOptions()
    {
        $client = $this->createAuthenticatedClient();

        $data =
<<<JSON
    {
        "code":"optionC",
        "attribute":"a_multi_select",
        "sort_order":30,
        "labels":{"en_US":"Option C"}
    }
JSON;

        $client->request('POST', '/api/rest/v1/attributes/a_multi_select/options', [], [], [], $data);

        $response = $client->getResponse();

        $this->assertSame(Response::HTTP_CREATED, $response->getStatusCode());
    }

    public function testAccessDeniedForEditingAttributeOptions()
    {
        $client = $this->createAuthenticatedClient([], [], null, null, 'julia', 'julia');

        $data =
<<<JSON
    {
        "code":"optionC",
        "attribute":"a_multi_select",
        "sort_order":30,
        "labels":{"en_US":"Option C"}
    }
JSON;

        $client->request('POST', '/api/rest/v1/attributes/a_multi_select/options', [], [], [], $data);

        $expectedResponse = [
            'code'    => 403,
            'message' => 'Access forbidden. You are not allowed to create or update attribute options.',
        ];

        $response = $client->getResponse();

        $this->assertSame(Response::HTTP_FORBIDDEN, $response->getStatusCode());
        $this->assertSame($expectedResponse, json_decode($response->getContent(), true));
    }

    public function testAccessGrantedForListingCategories()
    {
        $client = $this->createAuthenticatedClient();

        $client->request('GET', '/api/rest/v1/categories');

        $response = $client->getResponse();
        $this->assertSame(Response::HTTP_OK, $response->getStatusCode());
    }

    public function testAccessDeniedForListingCategories()
    {
        $client = $this->createAuthenticatedClient([], [], null, null, 'julia', 'julia');

        $client->request('GET', '/api/rest/v1/categories');

        $expectedResponse = [
            'code'    => 403,
            'message' => 'Access forbidden. You are not allowed to list categories.',
        ];

        $response = $client->getResponse();
        $this->assertSame(Response::HTTP_FORBIDDEN, $response->getStatusCode());
        $this->assertSame($expectedResponse, json_decode($response->getContent(), true));
    }

    public function testAccessGrantedForEditingCategories()
    {
        $client = $this->createAuthenticatedClient();

        $data =
<<<JSON
    {
        "code": "new_category"
    }
JSON;

        $client->request('POST', '/api/rest/v1/categories', [], [], [], $data);

        $response = $client->getResponse();
        $this->assertSame(Response::HTTP_CREATED, $response->getStatusCode());
    }

    public function testAccessDeniedForEditingCategories()
    {
        $client = $this->createAuthenticatedClient([], [], null, null, 'julia', 'julia');

        $data =
<<<JSON
    {
        "code": "super_new_category"
    }
JSON;

        $client->request('POST', '/api/rest/v1/categories', [], [], [], $data);

        $expectedResponse = [
            'code'    => 403,
            'message' => 'Access forbidden. You are not allowed to create or update categories.',
        ];

        $response = $client->getResponse();
        $this->assertSame(Response::HTTP_FORBIDDEN, $response->getStatusCode());
        $this->assertSame($expectedResponse, json_decode($response->getContent(), true));
    }

    public function testAccessGrantedForListingChannels()
    {
        $client = $this->createAuthenticatedClient();

        $client->request('GET', '/api/rest/v1/channels');

        $response = $client->getResponse();
        $this->assertSame(Response::HTTP_OK, $response->getStatusCode());
    }

    public function testAccessDeniedForListingChannels()
    {
        $client = $this->createAuthenticatedClient([], [], null, null, 'julia', 'julia');

        $client->request('GET', '/api/rest/v1/channels');

        $expectedResponse = [
            'code'    => 403,
            'message' => 'Access forbidden. You are not allowed to list channels.',
        ];

        $response = $client->getResponse();
        $this->assertSame(Response::HTTP_FORBIDDEN, $response->getStatusCode());
        $this->assertSame($expectedResponse, json_decode($response->getContent(), true));
    }

    public function testAccessGrantedForListingFamilies()
    {
        $client = $this->createAuthenticatedClient();

        $client->request('GET', '/api/rest/v1/families');

        $response = $client->getResponse();
        $this->assertSame(Response::HTTP_OK, $response->getStatusCode());
    }

    public function testAccessDeniedForListingFamilies()
    {
        $client = $this->createAuthenticatedClient([], [], null, null, 'julia', 'julia');

        $client->request('GET', '/api/rest/v1/families');

        $expectedResponse = [
            'code'    => 403,
            'message' => 'Access forbidden. You are not allowed to list families.',
        ];

        $response = $client->getResponse();
        $this->assertSame(Response::HTTP_FORBIDDEN, $response->getStatusCode());
        $this->assertSame($expectedResponse, json_decode($response->getContent(), true));
    }

    public function testAccessGrantedForEditingFamilies()
    {
        $client = $this->createAuthenticatedClient();

        $data =
<<<JSON
    {
        "code": "new_family"
    }
JSON;

        $client->request('POST', '/api/rest/v1/families', [], [], [], $data);

        $response = $client->getResponse();
        $this->assertSame(Response::HTTP_CREATED, $response->getStatusCode());
    }

    public function testAccessDeniedForEditingFamilies()
    {
        $client = $this->createAuthenticatedClient([], [], null, null, 'julia', 'julia');

        $data =
<<<JSON
    {
        "code": "super_new_family"
    }
JSON;

        $client->request('POST', '/api/rest/v1/families', [], [], [], $data);

        $expectedResponse = [
            'code'    => 403,
            'message' => 'Access forbidden. You are not allowed to create or update families.',
        ];

        $response = $client->getResponse();
        $this->assertSame(Response::HTTP_FORBIDDEN, $response->getStatusCode());
        $this->assertSame($expectedResponse, json_decode($response->getContent(), true));
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
