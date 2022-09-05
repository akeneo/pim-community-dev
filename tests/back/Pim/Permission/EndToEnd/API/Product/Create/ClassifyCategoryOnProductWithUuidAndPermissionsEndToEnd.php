<?php

namespace AkeneoTestEnterprise\Pim\Permission\EndToEnd\API\Product\Create;

use AkeneoTestEnterprise\Pim\Permission\EndToEnd\API\Product\AbstractProductTestCase;
use Ramsey\Uuid\Uuid;
use Ramsey\Uuid\UuidInterface;

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
class ClassifyCategoryOnProductWithUuidAndPermissionsEndToEnd extends AbstractProductTestCase
{
    public function testErrorProductWithNotGrantedCategory()
    {
        $client = $this->createAuthenticatedClient([], [], null, null, 'mary', 'mary');

        $data = '{"values": {"sku": [{"scope": null, "locale": null, "data": "my_product"}]}, "categories":["categoryB"]}';
        $client->request('POST', 'api/rest/v1/products-uuid', [], [], [], $data);
        $this->assertError422(
            $client->getResponse(),
            'Property \"categories\" expects a valid category code. The category does not exist, \"categoryB\" given',
            'post_products'
        );
    }

    public function testSuccessProductWithOnlyViewableCategory()
    {
        $client = $this->createAuthenticatedClient([], [], null, null, 'mary', 'mary');

        $data = '{"values": {"sku": [{"scope": null, "locale": null, "data": "my_product"}]}, "categories":["categoryA2"]}';
        $client->request('POST', 'api/rest/v1/products-uuid', [], [], [], $data);
        $response = $client->getResponse();
        $this->assertSame(201, $response->getStatusCode());
        $this->assertProduct('my_product', ['categoryA2']);
        $this->assertSame(
            sprintf(
                'http://localhost/api/rest/v1/products-uuid/%s',
                $this->getProductUuidFromIdentifier('my_product')
            ),
            $response->headers->get('location')
        );
    }

    public function testSuccessProductWithEditableCategory()
    {
        $client = $this->createAuthenticatedClient([], [], null, null, 'mary', 'mary');

        $data = '{"values": {"sku": [{"scope": null, "locale": null, "data": "my_product"}]}, "categories":["categoryA"]}';
        $client->request('POST', 'api/rest/v1/products-uuid', [], [], [], $data);
        $response = $client->getResponse();
        $this->assertSame(201, $response->getStatusCode());
        $this->assertProduct('my_product', ['categoryA']);
        $this->assertSame(
            sprintf(
                'http://localhost/api/rest/v1/products-uuid/%s',
                $this->getProductUuidFromIdentifier('my_product')
            ),
            $response->headers->get('location')
        );
    }

    public function testSuccessProductWithOwnCategory()
    {
        $client = $this->createAuthenticatedClient([], [], null, null, 'mary', 'mary');

        $data = '{"values": {"sku": [{"scope": null, "locale": null, "data": "my_product"}]}, "categories":["master"]}';
        $client->request('POST', 'api/rest/v1/products-uuid', [], [], [], $data);
        $response = $client->getResponse();
        $this->assertSame(201, $response->getStatusCode());
        $this->assertProduct('my_product', ['master']);
        $this->assertSame(sprintf(
            'http://localhost/api/rest/v1/products-uuid/%s',
            $this->getProductUuidFromIdentifier('my_product')
        ), $response->headers->get('location'));
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
            'parent'        => null,
            'groups'        => [],
            'categories'    => $categories,
            'enabled'       => true,
            'values'        => [
                'sku' => [['locale' => null, 'scope' => null, 'data' => $identifier]],
            ],
            'created'       => '2016-06-14T13:12:50+02:00',
            'updated'       => '2016-06-14T13:12:50+02:00',
            'associations'  => [],
            'quantified_associations' => [],
        ];

        $this->assertSameProducts($expectedProduct, $identifier);
    }
}
