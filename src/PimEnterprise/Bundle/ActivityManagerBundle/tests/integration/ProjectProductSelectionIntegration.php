<?php

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2016 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace PimEnterprise\Bundle\ActivityManagerBundle\tests\integration;

use PimEnterprise\Component\ActivityManager\Model\ProjectInterface;

class ProjectProductSelectionIntegration extends ActivityManagerTestCase
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
