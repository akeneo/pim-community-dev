<?php

namespace AkeneoTestEnterprise\Pim\Permission\EndToEnd\API\VariantProduct;

use Akeneo\Test\Integration\Configuration;
use PHPUnit\Framework\Assert;
use Akeneo\Tool\Bundle\ApiBundle\tests\integration\ApiTestCase;
use AkeneoTestEnterprise\Pim\Permission\EndToEnd\API\PermissionFixturesLoader;
use Symfony\Component\HttpFoundation\Response;

class DeleteVariantProductWithPermissionEndToEnd extends ApiTestCase
{
    /** @var PermissionFixturesLoader */
    private $loader;

    protected function setUp(): void
    {
        parent::setUp();

        $this->loader = new PermissionFixturesLoader($this->testKernel->getContainer());
    }

    public function testDeleteNotViewableVariantProduct()
    {
        $this->loader->loadProductModelsFixturesForCategoryPermissions();

        $client = $this->createAuthenticatedClient([], [], null, null, 'mary', 'mary');

        $client->request('DELETE', 'api/rest/v1/products/' . 'colored_sized_sweat_no_view', [], [], [], '{"categories": ["own_category"]}');
        $response = $client->getResponse();

        $expectedContent = sprintf('{"code":%d,"message":"Product \"colored_sized_sweat_no_view\" does not exist."}', Response::HTTP_NOT_FOUND);

        Assert::assertSame(Response::HTTP_NOT_FOUND, $response->getStatusCode());
        Assert::assertEquals($expectedContent, $response->getContent());
    }

    public function testDeleteOnlyViewableVariantProduct()
    {
        $this->loader->loadProductModelsFixturesForCategoryPermissions();

        $message = 'You can delete a product only if it is classified in at least one category on which you have an own permission.';
        $data = '{"categories": ["own_category"]}';

        $this->assertUnauthorized('colored_sized_shoes_view', $data, sprintf($message, 'colored_sized_shoes_view'));
        $this->assertUnauthorized('colored_sized_tshirt_view', $data, sprintf($message, 'colored_sized_tshirt_view'));
    }

    public function testDeleteOnlyEditableVariantProduct()
    {
        $this->loader->loadProductModelsFixturesForCategoryPermissions();

        $message = 'You can delete a product only if it is classified in at least one category on which you have an own permission.';
        $data = '{"categories": ["own_category"]}';

        $this->assertUnauthorized('colored_sized_sweat_edit', $data, sprintf($message, 'colored_sized_sweat_edit'));
        $this->assertUnauthorized('colored_sized_shoes_edit', $data, sprintf($message, 'colored_sized_shoes_edit'));
    }

    public function testDeleteOwnedVariantProduct()
    {
        $this->loader->loadProductModelsFixturesForCategoryPermissions();

        $data = '{"categories": ["own_category", "view_category"]}';
        $this->assertDeleted('colored_sized_sweat_own', $data);
        $this->assertDeleted('colored_sized_shoes_own', $data);
        $this->assertDeleted('colored_sized_trousers', $data);
    }

    /**
     * @param string $identifier                code of the product
     * @param string $data                      data submitted
     * @param string $sql                       SQL for database query
     * @param array  $expectedProductNormalized expected product data normalized in standard format
     */
    private function assertDeleted(string $identifier, string $data): void
    {
        $client = $this->createAuthenticatedClient([], [], null, null, 'mary', 'mary');

        $client->request('DELETE', 'api/rest/v1/products/' . $identifier, [], [], [], $data);
        Assert::assertSame(Response::HTTP_NO_CONTENT, $client->getResponse()->getStatusCode());

        $this->getFromTestContainer('doctrine')->getManager()->clear();
        $product = $this->getFromTestContainer('pim_catalog.repository.product')->findOneByIdentifier($identifier);

        Assert::assertNull($product);
    }

    /**
     * @param string $identifier
     * @param string $data
     * @param string $message
     */
    private function assertUnauthorized(string $identifier, string $data, string $message):void
    {
        $client = $this->createAuthenticatedClient([], [], null, null, 'mary', 'mary');

        $client->request('DELETE', 'api/rest/v1/products/' . $identifier, [], [], [], $data);
        $response = $client->getResponse();

        $expected = sprintf('{"code":%d,"message":"%s"}', Response::HTTP_FORBIDDEN, addslashes($message));

        Assert::assertSame(Response::HTTP_FORBIDDEN, $response->getStatusCode());
        Assert::assertEquals($expected, $response->getContent());
    }

    /**
     * {@inheritdoc}
     */
    protected function getConfiguration(): Configuration
    {
        return $this->catalog->useTechnicalCatalog();
    }
}
