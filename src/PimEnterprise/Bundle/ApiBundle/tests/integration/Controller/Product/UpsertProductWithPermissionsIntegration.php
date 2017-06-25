<?php

namespace PimEnterprise\Bundle\ApiBundle\tests\integration\Controller\Product;

use Symfony\Component\HttpFoundation\Response;

/**
 * +----------+-------------------------------+-----------------------------------+-----------------------------------+
 * |          |          Categories           |             Locales               |         Attribute groups          |
 * +  Roles   +-------------------------------+-----------------------------------+-----------------------------------+
 * |          |   categoryA2  |   categoryB   |   en_US   |   fr_FR   |   de_DE   | attributeGroupA | attributeGroupB |
 * +==========+===============================+===================================+===================================+
 * | Redactor |      View     |     -         | View,Edit |    View   |     -     |        -        |      View       |
 * | Manager  | View,Edit,Own | View,Edit,Own | View,Edit | View,Edit | View,Edit |    View,Edit    |    View,Edit    |
 * +================+===============================+===================================+=============================+
 */
class UpsertProductWithPermissionsIntegration extends AbstractProductTestCase
{
    public function testToMergeNotGrantedCategoryWithANewCategory()
    {
        $client = $this->createAuthenticatedClient([], [], null, null, 'mary', 'mary');

        $data = <<<JSON
{
    "categories": ["categoryA1", "categoryA2"]
}
JSON;

        $client->request('PATCH', 'api/rest/v1/products/product_viewable_by_everybody_2', [], [], [], $data);
        $this->assertSame(Response::HTTP_NO_CONTENT, $client->getResponse()->getStatusCode());

        $product = $this->get('pim_catalog.repository.product')->findOneByIdentifier('product_viewable_by_everybody_2');

        $codes = array_map(function ($category) {
            return $category->getCode();
        }, $product->getCategories()->toArray());
        sort($codes);

        $this->assertEquals(['categoryA1', 'categoryA2', 'categoryB'], $codes);
    }
}
