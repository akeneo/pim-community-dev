<?php

namespace AkeneoTestEnterprise\Pim\Permission\EndToEnd\API\Products\ListProducts;

use Akeneo\Test\Integration\Configuration;
use Doctrine\DBAL\Connection;
use PHPUnit\Framework\Assert;
use Akeneo\Tool\Bundle\ApiBundle\tests\integration\ApiTestCase;
use AkeneoTest\Pim\Enrichment\Integration\Normalizer\NormalizedProductCleaner;
use AkeneoTestEnterprise\Pim\Permission\EndToEnd\API\PermissionFixturesLoader;
use Ramsey\Uuid\Uuid;
use Ramsey\Uuid\UuidInterface;
use Symfony\Component\HttpFoundation\Response;

class ListProductWithUuidAndPermissionEndToEnd extends ApiTestCase
{
    /** @var PermissionFixturesLoader */
    private $loader;

    protected function setUp(): void
    {
        parent::setUp();

        $this->loader = $this->get('akeneo_integration_tests.loader.permissions');
    }

    /**
     * @group critical
     */
    public function test_get_list_of_products_by_applying_permissions()
    {
        $this->loader->loadProductModelsFixturesForCategoryPermissions();

        $client = $this->createAuthenticatedClient([], [], null, null, 'mary', 'mary');

        $client->request('GET', 'api/rest/v1/products-uuid?limit=20');
        $response = $client->getResponse();

        Assert::assertSame(Response::HTTP_OK, $response->getStatusCode());

        $content = json_decode($response->getContent(), true);
        $uuids = array_map(function($item){
            return $item['uuid'];
        }, $content['_embedded']['items']);
        sort($uuids);

        $expectedUuids = $this->getUuidsFromIdentifiers([
            'colored_sized_shoes_view',
            'colored_sized_tshirt_view',
            'colored_sized_sweat_edit',
            'colored_sized_shoes_edit',
            'colored_sized_sweat_own',
            'colored_sized_shoes_own',
            'colored_sized_trousers'
        ]);
        sort($expectedUuids);
        Assert::assertSame($expectedUuids, $uuids);
    }

    /**
     * @param Response $response
     * @param string   $expected
     */
    protected function assertListResponse(Response $response, $expected)
    {
        $result = json_decode($response->getContent(), true);
        $expected = json_decode($expected, true);

        if (!isset($result['_embedded'])) {
            \PHPUnit_Framework_Assert::fail($response->getContent());
        }

        foreach ($result['_embedded']['items'] as $index => $product) {
            NormalizedProductCleaner::clean($result['_embedded']['items'][$index]);

            if (isset($expected['_embedded']['items'][$index])) {
                NormalizedProductCleaner::clean($expected['_embedded']['items'][$index]);
            }
        }

        $this->assertEquals($expected, $result);
    }

    /**
     * {@inheritdoc}
     */
    protected function getConfiguration(): Configuration
    {
        return $this->catalog->useTechnicalCatalog();
    }

    private function getUuidsFromIdentifiers(array $identifiers): array
    {
        return $this->get('database_connection')->fetchFirstColumn(
            'SELECT BIN_TO_UUID(uuid) AS uuid FROM pim_catalog_product WHERE identifier IN (:identifiers)',
            ['identifiers' => $identifiers],
            ['identifiers' => Connection::PARAM_STR_ARRAY]
        );
    }
}
