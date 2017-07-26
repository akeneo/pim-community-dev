<?php

namespace PimEnterprise\Bundle\ApiBundle\tests\integration\Controller\Product\Create;

use PimEnterprise\Bundle\ApiBundle\tests\integration\Controller\Product\AbstractProductTestCase;
use Symfony\Component\HttpFoundation\Response;

/**
 * +----------+-----------------------------------------------+
 * |          |          Categories                           |
 * +  Roles   +-----------------------------------------------+
 * |          |   categoryA   |  categoryA2   |   categoryB   |
 * +==========+===============+===============+===============+
 * | Redactor |   View,Edit   |     View      |     -         |
 * | Manager  | View,Edit,Own | View,Edit,Own | View,Edit,Own |
 * +==========+===============+===============+===============+
 */
class ClassifyCategoryOnProductWithPermissionsIntegration extends AbstractProductTestCase
{
    public function testErrorProductWithNotGrantedCategory()
    {
        $client = $this->createAuthenticatedClient([], [], null, null, 'mary', 'mary');

        $data = '{"identifier": "my_product", "categories":["categoryB"]}';
        $client->request('POST', 'api/rest/v1/products', [], [], [], $data);
        $this->assertError422($client->getResponse(), 'Property \"categories\" expects a valid category code. The category does not exist, \"categoryB\" given');
    }

    public function testSuccessProductWithOnlyViewableCategory()
    {
        $client = $this->createAuthenticatedClient([], [], null, null, 'mary', 'mary');

        $data = '{"identifier": "my_product", "categories":["categoryA2"]}';
        $client->request('POST', 'api/rest/v1/products', [], [], [], $data);
        $response = $client->getResponse();
        $this->assertSame(201, $response->getStatusCode());
        $this->assertProduct('my_product', ['categoryA2']);
    }

    public function testSuccessProductWithEditableCategory()
    {
        $client = $this->createAuthenticatedClient([], [], null, null, 'mary', 'mary');

        $data = '{"identifier": "my_product", "categories":["categoryA"]}';
        $client->request('POST', 'api/rest/v1/products', [], [], [], $data);
        $response = $client->getResponse();
        $this->assertSame(201, $response->getStatusCode());
        $this->assertProduct('my_product', ['categoryA']);
    }

    public function testSuccessProductWithOwnCategory()
    {
        $client = $this->createAuthenticatedClient([], [], null, null, 'mary', 'mary');

        $data = '{"identifier": "my_product", "categories":["master"]}';
        $client->request('POST', 'api/rest/v1/products', [], [], [], $data);
        $response = $client->getResponse();
        $this->assertSame(201, $response->getStatusCode());
        $this->assertProduct('my_product', ['master']);
    }

    /**
     * @param string $identifier
     * @param array  $categories
     */
    private function assertProduct($identifier, array $categories)
    {
        $expectedProduct = [
            'identifier'    => $identifier,
            'family'        => null,
            'groups'        => [],
            'variant_group' => null,
            'categories'    => $categories,
            'enabled'       => true,
            'values'        => [
                'sku' => [['locale' => null, 'scope' => null, 'data' => $identifier]],
            ],
            'created'       => '2016-06-14T13:12:50+02:00',
            'updated'       => '2016-06-14T13:12:50+02:00',
            'associations'  => [],
        ];

        $this->assertSameProducts($expectedProduct, $identifier);
    }
}
