<?php

namespace Akeneo\Pim\WorkOrganization\Workflow\Bundle\tests\EndToEnd\PublishedProduct;

use Symfony\Component\HttpFoundation\Response;

class DeletePublishedProductIntegration extends AbstractPublishedProductTestCase
{
    public function testDeleteAPublishedProductFails()
    {
        $product = $this->get('pim_catalog.repository.product')->findOneByIdentifier('product_viewable_by_everybody_1');
        $this->get('pimee_workflow.manager.published_product')->publish($product);

        $expectedResponseContent =
            <<<JSON
{"code":422,"message":"Impossible to remove a published product"}
JSON;

        $client = $this->createAuthenticatedClient([], [], null, null, 'julia', 'julia');
        $client->request('DELETE', 'api/rest/v1/products/product_viewable_by_everybody_1');

        $response = $client->getResponse();
        $this->assertSame(Response::HTTP_UNPROCESSABLE_ENTITY, $response->getStatusCode());
        $this->assertSame($expectedResponseContent, $response->getContent());
        $this->assertProductNotDeleted('product_viewable_by_everybody_1');
    }
}
