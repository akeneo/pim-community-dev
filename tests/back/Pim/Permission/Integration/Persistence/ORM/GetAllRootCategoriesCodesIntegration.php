<?php

declare(strict_types=1);

namespace AkeneoTestEnterprise\Pim\Permission\Integration\Persistence\ORM;

use Akeneo\Pim\Permission\Bundle\Persistence\ORM\Category\GetAllRootCategoriesCodes;
use Akeneo\Test\Integration\Configuration;
use Akeneo\Test\Integration\TestCase;

class GetAllRootCategoriesCodesIntegration extends TestCase
{
    private GetAllRootCategoriesCodes $query;

    /**
     * {@inheritdoc}
     */
    protected function getConfiguration(): Configuration
    {
        return $this->catalog->useMinimalCatalog();
    }

    protected function setUp(): void
    {
        parent::setUp();

        $this->query = $this->get(GetAllRootCategoriesCodes::class);
    }

    public function testItFetchesAllRootCategoriesByCode(): void
    {
        $expected = [
            'a_tree',
            'b_tree',
            'master',
        ];

        $this->createCategory(['code' => 'a_tree']);
        $this->createCategory(['code' => 'master_child_A', 'parent' => 'master']);
        $this->createCategory(['code' => 'a_tree_child_A', 'parent' => 'a_tree']);
        $this->createCategory(['code' => 'a_tree_child_B', 'parent' => 'a_tree']);
        $this->createCategory(['code' => 'a_tree_child_C', 'parent' => 'a_tree_child_B']);
        $this->createCategory(['code' => 'a_tree_child_D', 'parent' => 'a_tree_child_C']);
        $this->createCategory(['code' => 'b_tree']);
        $this->createCategory(['code' => 'b_tree_child_A', 'parent' => 'b_tree']);

        $results = $this->query->execute();

        $this->assertEqualsCanonicalizing($expected, $results, 'Categories codes are not matched');
    }
}
