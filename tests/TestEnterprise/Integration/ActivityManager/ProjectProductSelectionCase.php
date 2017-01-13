<?php

namespace Akeneo\TestEnterprise\Integration\ActivityManager;

use PimEnterprise\Component\ActivityManager\Model\ProjectInterface;

class ProjectProductSelectionCase extends ActivityManagerTestCase
{
    /**
     * Create a project with only one product to test that the pre processed data are well calculated for a product
     * with no categories
     *
     * Product : categoryless
     * Channel: ecommerce
     * Locale: en_US
     */
    public function testProductWithoutCategoriesShouldBeProcessed()
    {
        $project = $this->createProject([
            'label' => 'categoriesless-project',
            'locale' => 'en_US',
            'owner'=> 'admin',
            'channel' => 'ecommerce',
            'product_filters' =>[
                [
                    'field' => 'sku',
                    'operator' => '=',
                    'value' => 'categoriesless',
                    'context' => ['locale' => 'en_US', 'scope' => 'ecommerce'],
                ],
            ],
        ]);

        $this->calculateProject($project);
        $this->checkLinkProjectProduct($project);
    }

    /**
     * Checks the link between a product and a project.
     *
     * @param ProjectInterface $project
     */
    protected function checkLinkProjectProduct(ProjectInterface $project)
    {
        $productId = $this->get('pim_catalog.repository.product')
            ->findOneByIdentifier('categoriesless')
            ->getId();

        $sql = <<<SQL
SELECT COUNT(*)
FROM `pimee_activity_manager_project_product`
WHERE `project_id` = :project_id
AND `product_id` = :product_id
SQL;

        $numberOfRow = (int) $this->getConnection()->fetchColumn($sql, [
            'project_id' => $project->getId(),
            'product_id' => $productId,
        ]);

        $this->assertSame(
            $numberOfRow,
            1,
            sprintf('Invalid number of products for the project %s', $project->getId())
        );
    }
}
