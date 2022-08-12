<?php

declare(strict_types=1);

namespace Akeneo\Catalogs\Test\Integration\Infrastructure\Controller\Internal;

use Akeneo\Catalogs\Test\Integration\IntegrationTestCase;
use PHPUnit\Framework\Assert;

/**
 * @copyright 2022 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class GetCategoryTreeRootsActionTest extends IntegrationTestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        $this->purgeDataAndLoadMinimalCatalog();
    }

    public function testItGetsCategoryTreeRoots(): void
    {
        $client = $this->getAuthenticatedInternalApiClient();

        $masterCategory = $this->getCategory('master');
        $tshirtCategory = $this->createCategory(['code' => 'tshirt', 'labels' => ['en_US' => 'T-shirt']]);
        $this->createCategory([
            'code' => 'tanktop',
            'parent' => 'tshirt',
            'labels' => ['en_US' => 'T-shirt']
        ]);

        $client->request(
            'GET',
            '/rest/catalogs/categories/tree-roots',
            [],
            [],
            [
                'HTTP_X-Requested-With' => 'XMLHttpRequest',
            ],
        );

        $response = $client->getResponse();
        Assert::assertEquals(200, $response->getStatusCode());

        $categories = \json_decode($response->getContent(), true);

        $expectedMasterCategory = [
            'id' => $masterCategory->getId(),
            'code' => 'master',
            'label' => 'Master catalog',
            'isLeaf' => false,
        ];

        $expectedTshirtCategory = [
            'id' => $tshirtCategory->getId(),
            'code' => $tshirtCategory->getCode(),
            'label' => $tshirtCategory->getLabel(),
            'isLeaf' => false,
        ];

        Assert::assertEquals([$expectedMasterCategory, $expectedTshirtCategory], $categories);
    }
}
