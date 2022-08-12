<?php

declare(strict_types=1);

namespace Akeneo\Catalogs\Test\Integration\Infrastructure\Controller\Internal;

use Akeneo\Catalogs\Test\Integration\IntegrationTestCase;
use PHPUnit\Framework\Assert;

/**
 * @copyright 2022 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class GetCategoriesActionTest extends IntegrationTestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        $this->purgeDataAndLoadMinimalCatalog();
    }

    public function testItGetsCategories(): void
    {
        $client = $this->getAuthenticatedInternalApiClient();

        $tshirtCategory = $this->createCategory(['code' => 'tshirt', 'labels' => ['en_US' => 'T-shirt']]);
        $shoesCategory = $this->createCategory(['code' => 'shoes', 'labels' => ['en_US' => 'Shoes']]);

        $client->request(
            'GET',
            '/rest/catalogs/categories',
            ['codes' => 'tshirt,shoes'],
            [],
            [
                'HTTP_X-Requested-With' => 'XMLHttpRequest',
            ],
        );

        $response = $client->getResponse();
        Assert::assertEquals(200, $response->getStatusCode());

        $categories = \json_decode($response->getContent(), true);

        $expectedTshirtCategory = [
            'id' => $tshirtCategory->getId(),
            'code' => 'tshirt',
            'label' => 'T-shirt',
            'isLeaf' => true,
        ];

        $expectedShoesCategory = [
            'id' => $shoesCategory->getId(),
            'code' => 'shoes',
            'label' => 'Shoes',
            'isLeaf' => true,
        ];

        Assert::assertEquals([$expectedShoesCategory, $expectedTshirtCategory], $categories);
    }

    public function testItGetsAnEmptyListWhenNoCodesGiven(): void
    {
        $client = $this->getAuthenticatedInternalApiClient('admin');

        $this->createCategory(['code' => 'tshirt', 'labels' => ['en_US' => 'T-shirt']]);

        $client->request(
            'GET',
            '/rest/catalogs/categories',
            [],
            [],
            [
                'HTTP_X-Requested-With' => 'XMLHttpRequest',
            ],
        );

        $response = $client->getResponse();
        Assert::assertEquals(200, $response->getStatusCode());

        $categories = \json_decode($response->getContent(), true);

        Assert::assertEmpty($categories, 'No categories should be returned');
    }
}
