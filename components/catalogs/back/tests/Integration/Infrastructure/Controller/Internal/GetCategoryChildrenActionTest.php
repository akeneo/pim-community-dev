<?php

declare(strict_types=1);

namespace Akeneo\Catalogs\Test\Integration\Infrastructure\Controller\Internal;

use Akeneo\Catalogs\Test\Integration\IntegrationTestCase;
use PHPUnit\Framework\Assert;

/**
 * @copyright 2022 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 *
 * @covers \Akeneo\Catalogs\Infrastructure\Controller\Internal\GetCategoryChildrenAction
 */
class GetCategoryChildrenActionTest extends IntegrationTestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        $this->purgeDataAndLoadMinimalCatalog();
    }

    public function testItGetsAListOfCategoryChildrenForAGivenParentId(): void
    {
        $client = $this->getAuthenticatedInternalApiClient();

        $this->createCategory(['code' => 'parent_category', 'parent' => 'master']);
        $this->createCategory([
            'code' => 'child1',
            'parent' => 'parent_category',
            'labels' => ['en_US' => 'Child category'],
        ]);

        $this->createCategory([
            'code' => 'child2',
            'parent' => 'parent_category',
            'labels' => ['en_US' => 'Child 2 category'],
        ]);

        $this->createCategory(['code' => 'grand_child', 'parent' => 'child1']);

        $client->request(
            'GET',
            '/rest/catalogs/categories/parent_category/children',
            [],
            [],
            [
                'HTTP_X-Requested-With' => 'XMLHttpRequest',
            ],
        );

        $response = $client->getResponse();
        Assert::assertEquals(200, $response->getStatusCode());

        $children = \json_decode($response->getContent(), true, 512, JSON_THROW_ON_ERROR);

        Assert::assertEquals([
            [
                'code' => 'child1',
                'label' => 'Child category',
                'isLeaf' => false,
            ],
            [
                'code' => 'child2',
                'label' => 'Child 2 category',
                'isLeaf' => true,
            ],
        ], $children);
    }
}
