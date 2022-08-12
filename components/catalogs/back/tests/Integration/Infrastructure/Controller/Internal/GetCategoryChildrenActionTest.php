<?php

declare(strict_types=1);

namespace Akeneo\Catalogs\Test\Integration\Infrastructure\Controller\Internal;

use Akeneo\Catalogs\Test\Integration\IntegrationTestCase;
use PHPUnit\Framework\Assert;

/**
 * @copyright 2022 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
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

        $parentCategory = $this->createCategory(['code' => 'parent_category', 'parent' => 'master']);
        $child1 = $this->createCategory([
            'code' => 'child1',
            'parent' => 'parent_category',
            'labels' => ['en_US' => 'Child category']
        ]);

        $child2 = $this->createCategory([
            'code' => 'child2',
            'parent' => 'parent_category',
            'labels' => ['en_US' => 'Child 2 category']
        ]);

        $this->createCategory(['code' => 'grand_child', 'parent' => 'child1']);

        $client->request(
            'GET',
            "/rest/catalogs/categories/{$parentCategory->getId()}/children",
            [],
            [],
            [
                'HTTP_X-Requested-With' => 'XMLHttpRequest',
            ],
        );

        $response = $client->getResponse();
        Assert::assertEquals(200, $response->getStatusCode());

        $children = \json_decode($response->getContent(), true);

        $expectedChild1 = [
            'id' => $child1->getId(),
            'code' => 'child1',
            'label' => 'Child category',
            'isLeaf' => false,
        ];

        $expectedChild2 = [
            'id' => $child2->getId(),
            'code' => 'child2',
            'label' => 'Child 2 category',
            'isLeaf' => true,
        ];

        Assert::assertEquals([$expectedChild1, $expectedChild2], $children);
    }
}
