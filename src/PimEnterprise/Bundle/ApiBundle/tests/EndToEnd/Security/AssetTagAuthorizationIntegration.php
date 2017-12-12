<?php

namespace PimEnterprise\Bundle\ApiBundle\tests\EndToEnd\Security;

use Akeneo\Component\Classification\Model\TagInterface;
use Pim\Bundle\ApiBundle\tests\integration\ApiTestCase;
use Symfony\Component\HttpFoundation\Response;

/**
 * @author    Philippe Mossière <philippe.mossiere@akeneo.com>
 */
class AssetTagAuthorizationIntegration extends ApiTestCase
{
    /**
     * Should be an integration test.
     */
    public function testOverallAccessDenied()
    {
        $client = $this->createAuthenticatedClient([], [], null, null, 'kevin', 'kevin');

        $client->request('GET', '/api/rest/v1/asset-tags/michel');

        $expectedResponse =
<<<JSON
{
    "code": 403,
    "message": "You are not allowed to access the web API."
}
JSON;

        $response = $client->getResponse();
        $this->assertSame(Response::HTTP_FORBIDDEN, $response->getStatusCode());
        $this->assertJsonStringEqualsJsonString($expectedResponse, $response->getContent());
    }

    /**
     * Should be an integration test.
     */
    public function testAccessGrantedForGettingATag()
    {
        $this->createTag(['code' => 'a_tag']);

        $client = $this->createAuthenticatedClient([], [], null, null, 'julia', 'julia');

        $client->request('GET', '/api/rest/v1/asset-tags/a_tag');

        $response = $client->getResponse();
        $this->assertSame(Response::HTTP_OK, $response->getStatusCode());
    }

    /**
     * Creates an asset tag with data.
     *
     * @param array $data
     *
     * @return TagInterface
     */
    private function createTag(array $data): TagInterface
    {
        $tag = $this->get('pimee_product_asset.factory.tag')->create();

        $this->get('pimee_product_asset.updater.tag')->update($tag, $data);

        $errors = $this->get('validator')->validate($tag);
        $this->assertCount(0, $errors);

        $this->get('pimee_product_asset.saver.tag')->save($tag);

        return $tag;
    }

    /**
     * {@inheritdoc}
     */
    protected function getConfiguration()
    {
        return $this->catalog->useTechnicalCatalog();
    }
}
