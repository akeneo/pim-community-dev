<?php

namespace AkeneoTestEnterprise\Pim\WorkOrganization\EndToEnd\Workflow\PublishedProduct;

use AkeneoTest\Pim\Enrichment\Integration\Normalizer\NormalizedProductCleaner;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Core\Authentication\Token\UsernamePasswordToken;

class GetPublishedProductEndToEnd extends AbstractPublishedProductTestCase
{
    public function testGetACompletePublishedProduct()
    {
        $client = $this->createAuthenticatedClient([], [], null, null, 'admin', 'admin');

        $user = $this->get('pim_user.provider.user')->loadUserByUsername('admin');
        $token = new UsernamePasswordToken($user, 'main', $user->getRoles());
        $this->get('security.token_storage')->setToken($token);

        $product = $this->get('pim_catalog.repository.product')->findOneByIdentifier('product_viewable_by_everybody_1');
        $this->publishProduct($product);

        $client->request('GET', 'api/rest/v1/published-products/product_viewable_by_everybody_1');

        $standardPublishedProducts = $this->getStandardizedPublishedProducts();

        $response = $client->getResponse();
        $this->assertSame(Response::HTTP_OK, $response->getStatusCode());
        $this->assertResponse($response, $standardPublishedProducts['product_viewable_by_everybody_1']);
    }

    public function testNotFoundAPublishedProduct()
    {
        $client = $this->createAuthenticatedClient();

        $client->request('GET', 'api/rest/v1/published-products/not_found');

        $expectedContent =
            <<<JSON
    {
        "code": 404,
        "message": "Published product \"not_found\" does not exist or you do not have permission to access it."
    }
JSON;

        $response = $client->getResponse();
        $this->assertSame(Response::HTTP_NOT_FOUND, $response->getStatusCode());
        $this->assertJsonStringEqualsJsonString($expectedContent, $response->getContent());
    }

    /**
     * @param Response $response
     * @param string   $expected
     */
    private function assertResponse(Response $response, string $expected)
    {
        $result = json_decode($response->getContent(), true);
        $expected = json_decode($expected, true);
        unset($expected['_links']);

        NormalizedProductCleaner::clean($expected);
        NormalizedProductCleaner::clean($result);

        $this->assertEquals($expected, $result);
    }
}
