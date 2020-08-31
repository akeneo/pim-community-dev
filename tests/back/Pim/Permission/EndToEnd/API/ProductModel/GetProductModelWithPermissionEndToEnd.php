<?php

namespace AkeneoTestEnterprise\Pim\Permission\EndToEnd\API\ProductModel;

use Akeneo\Test\Integration\Configuration;
use PHPUnit\Framework\Assert;
use Akeneo\Tool\Bundle\ApiBundle\tests\integration\ApiTestCase;
use AkeneoTest\Pim\Enrichment\Integration\Normalizer\NormalizedProductCleaner;
use AkeneoTestEnterprise\Pim\Permission\EndToEnd\API\PermissionFixturesLoader;
use Symfony\Component\HttpFoundation\Response;

class GetProductModelWithPermissionEndToEnd extends ApiTestCase
{
    /** @var PermissionFixturesLoader */
    private $loader;

    protected function setUp(): void
    {
        parent::setUp();

        $this->loader = $this->get('akeneo_integration_tests.loader.permissions');
    }

    public function testGetNotViewableProductModel()
    {
        $this->loader->loadProductModelsFixturesForCategoryPermissions();

        $message = 'Product model "%s" does not exist or you do not have permission to access it.';
        $this->assertUnauthorized('sweat_no_view', sprintf($message, 'sweat_no_view'));
        $this->assertUnauthorized('colored_sweat_no_view', sprintf($message, 'colored_sweat_no_view'));
        $this->assertUnauthorized('shoes_no_view', sprintf($message, 'shoes_no_view'));
        $this->assertUnauthorized('jacket_no_view', sprintf($message, 'jacket_no_view'));
    }

    public function testGetViewableProductModel()
    {
        $this->loader->loadProductModelsFixturesForCategoryPermissions();

        $this->assertAuthorized('shoes_view');
        $this->assertAuthorized('tshirt_view');
        $this->assertAuthorized('sweat_edit');
        $this->assertAuthorized('shoes_own');
        $this->assertAuthorized('trousers');
        $this->assertAuthorized('colored_shoes_view');
        $this->assertAuthorized('colored_tshirt_view');
        $this->assertAuthorized('colored_sweat_edit');
        $this->assertAuthorized('colored_shoes_edit');
        $this->assertAuthorized('colored_shoes_own');
        $this->assertAuthorized('colored_trousers');
    }

    /**
     * @param string $code
     * @param string $message
     */
    private function assertUnauthorized(string $code, string $message)
    {
        $client = $this->createAuthenticatedClient([], [], null, null, 'mary', 'mary');

        $client->request('GET', 'api/rest/v1/product-models/' . $code);
        $response = $client->getResponse();

        $expected = sprintf('{"code":%d,"message":"%s"}', Response::HTTP_NOT_FOUND, addslashes($message));

        Assert::assertSame(Response::HTTP_NOT_FOUND, $response->getStatusCode());
        Assert::assertEquals($expected, $response->getContent());
    }

    /**
     * @param string $code
     */
    private function assertAuthorized(string $code)
    {
        $client = $this->createAuthenticatedClient([], [], null, null, 'mary', 'mary');

        $client->request('GET', 'api/rest/v1/product-models/' . $code);
        $response = $client->getResponse();

        Assert::assertSame(Response::HTTP_OK, $response->getStatusCode(), sprintf('Error with "%s".', $code));
    }

    /**
     * @param Response $response
     * @param string   $expected
     */
    private function assertResponse(Response $response, $expected)
    {
        $result = json_decode($response->getContent(), true);
        $expected = json_decode($expected, true);

        NormalizedProductCleaner::clean($expected);
        NormalizedProductCleaner::clean($result);

        $this->assertEquals($expected, $result);
    }

    /**
     * {@inheritdoc}
     */
    protected function getConfiguration(): Configuration
    {
        return $this->catalog->useTechnicalCatalog();
    }
}
