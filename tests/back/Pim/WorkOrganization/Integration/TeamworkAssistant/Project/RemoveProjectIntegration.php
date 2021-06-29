<?php

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2017 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace AkeneoTestEnterprise\Pim\WorkOrganization\Integration\TeamworkAssistant\Project;

use AkeneoTestEnterprise\Pim\WorkOrganization\Integration\TeamworkAssistant\TeamworkAssistantTestCase;

class RemoveProjectIntegration extends TeamworkAssistantTestCase
{
    /**
     * Tests if pre processing entries are well removed after a project removal and if products associated
     * to the project are well removed from the mapping table.
     * Pre-processing entries that have to be removed are rows containing products associated to the removed project
     * AND not associated to another project.
     */
    public function testThatProjectRemovalRemovesPreProcessingEntriesAndMappedProducts()
    {
        $highTechProject = $this->createProject('High-Tech project', 'admin', 'en_US', 'ecommerce', [
            [
                'field'    => 'categories',
                'operator' => 'IN',
                'value'    => ['high_tech'],
            ],
        ]);

        $this->createProject('Clothing project', 'admin', 'en_US', 'ecommerce', [
            [
                'field'    => 'categories',
                'operator' => 'IN',
                'value'    => ['clothing'],
            ],
        ]);

        $projectId = $highTechProject->getId();
        $this->removeProject($highTechProject);

        $this->assertPreProcessingEntriesAreRemoved();
        $this->assertAssociatedProductsAreRemoved($projectId);
    }

    /**
     * Test if datagrid view of a project is deleted on a project deletion.
     */
    public function testThatProjectRemovalRemovesAssociatedDatagridView()
    {
        $project = $this->createProject('High-Tech project', 'admin', 'en_US', 'ecommerce', [
            [
                'field'    => 'categories',
                'operator' => 'IN',
                'value'    => ['high_tech'],
            ],
        ]);

        $viewId = $project->getDatagridView()->getId();

        $this->removeProject($project);

        $view = $this->get('pim_datagrid.repository.datagrid_view')->find($viewId);
        $this->assertTrue(null === $view, 'View should be deleted on a project deletion, but is still in database.');
    }

    /**
     * Checks if pre processing entries are well removed after a project removal.
     * Entries that have to be removed are rows containing products associated to the removed project
     * AND not associated to another project.
     */
    private function assertPreProcessingEntriesAreRemoved()
    {
        $selectPreProcessing = <<<SQL
SELECT count(`completeness`.`product_id`)
FROM `pimee_teamwork_assistant_completeness_per_attribute_group` AS `completeness`;
SQL;

        $preProcessingEntries = (int) $this->getConnection()->fetchColumn($selectPreProcessing);
        $this->assertSame(
            $preProcessingEntries,
            9,
            sprintf(
                'Must have 9 rows in pre processing table after project removal, found "%s".',
                $preProcessingEntries
            )
        );

        $selectProductsPreProcessing = <<<SQL
SELECT DISTINCT(`completeness`.`product_id`)
FROM `pimee_teamwork_assistant_completeness_per_attribute_group` AS `completeness`;
SQL;
        $productsIdPreProcessing = $this->getConnection()->fetchAll($selectProductsPreProcessing);
        $clothingProductsId = $this->getFormattedClothingProductIds();

        $this->assertCount(count($productsIdPreProcessing), $clothingProductsId);
        foreach ($productsIdPreProcessing as $productId) {
            $this->assertTrue(in_array($productId, $clothingProductsId));
        }
    }

    /**
     * Checks if products associated to the given project are well removed from the mapping table.
     *
     * @param int $projectId
     */
    private function assertAssociatedProductsAreRemoved($projectId)
    {
        $selectProducts = <<<SQL
SELECT count(`project_product`.`product_id`) AS `count`
FROM `pimee_teamwork_assistant_project_product` AS `project_product`
WHERE `project_product`.`project_id` = :project_id;
SQL;

        $projectProductsResult = (int) $this->getConnection()
            ->fetchColumn($selectProducts, ['project_id' => $projectId]);

        $this->assertSame(
            $projectProductsResult,
            0,
            sprintf(
                'Project product table should be empty. "%s" products associated to the removed project found.',
                $projectProductsResult
            )
        );
    }

    /**
     * @return array
     */
    private function getFormattedClothingProductIds()
    {
        $pqbFactory = $this->get('pim_catalog.query.product_query_builder_factory');
        $pqb = $pqbFactory->create([
            'filters' => [
                [
                    'field'    => 'categories',
                    'operator' => 'IN',
                    'value'    => ['clothing'],
                    'context'  => ['locale' => 'en_US', 'scope' => 'ecommerce']
                ]
            ]
        ]);
        $productsId = [];
        foreach ($pqb->execute() as $product) {
            $productsId[] = ['product_id' => $product->getId()];
        }

        return $productsId;
    }
}
