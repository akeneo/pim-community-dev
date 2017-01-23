<?php

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2017 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace PimEnterprise\Bundle\ActivityManagerBundle\tests\integration;

use PimEnterprise\Component\ActivityManager\Model\ProjectInterface;

class ProjectProductSelectionIntegration extends ActivityManagerTestCase
{
    /**
     * Test that the pre processed data are well calculated for products without any category
     *
     * Family : tshirt (4 products with one uncategorized product)
     * User: Julia (catalog mananger)
     * Channel: ecommerce
     * Locale: en_US
     */
    public function testTheNumberOfProductForTshirtFamily()
    {
        $project = $this->createProject([
            'label'           => 'test-thsirt',
            'locale'          => 'en_US',
            'owner'           => 'Julia',
            'channel'         => 'ecommerce',
            'product_filters' => [
                [
                    'field'    => 'family',
                    'operator' => 'IN',
                    'value'    => ['tshirt'],
                    'context'  => ['locale' => 'en_US', 'scope' => 'ecommerce'],
                ],
            ],
        ]);

        $this->calculateProject($project);
        $this->checkNumberOfProduct($project, 4);
    }

    /**
     * Test that the pre processed data are well calculated for products without any category
     *
     * Product : categoryless
     * User: Julia (catalog mananger)
     * Channel: ecommerce
     * Locale: en_US
     */
    public function testTheNumberOfProductForTechnicalFamily()
    {
        $project = $this->createProject([
            'label'           => 'test-technical-family',
            'locale'          => 'en_US',
            'owner'           => 'Julia',
            'channel'         => 'ecommerce',
            'product_filters' => [
                [
                    'field'    => 'family',
                    'operator' => 'IN',
                    'value'    => ['technical_family'],
                    'context'  => ['locale' => 'en_US', 'scope' => 'ecommerce'],
                ],
            ],
        ]);

        $this->calculateProject($project);
        $this->checkNumberOfProduct($project, 2);
    }

    /**
     * Test that the pre processed data are well calculated depending on the project creator permissions
     *
     * Family : tshirt (4 products with one uncategorized product)
     * User: Teddy only the hight tech category (1 tshirt + 1 uncategorized tshirt)
     * Channel: ecommerce
     * Locale: en_US
     */
    public function testTheNumberOfProductForTshirtFamilyForTeddy()
    {
        $project = $this->createProject([
            'label'           => 'test-project-creator-right',
            'locale'          => 'en_US',
            'owner'           => 'Teddy',
            'channel'         => 'ecommerce',
            'product_filters' => [
                [
                    'field'    => 'family',
                    'operator' => 'IN',
                    'value'    => ['tshirt'],
                    'context'  => ['locale' => 'en_US', 'scope' => 'ecommerce'],
                ],
            ],
        ]);

        $this->calculateProject($project);
        $this->checkNumberOfProduct($project, 2);
    }

    /**
     * Checks the link between a product and a project.
     *
     * @param ProjectInterface $project
     * @param int              $expectedCount
     */
    private function checkNumberOfProduct(ProjectInterface $project, $expectedCount)
    {
        $sql = <<<SQL
SELECT COUNT(*)
FROM `pimee_activity_manager_project_product`
WHERE `project_id` = :project_id
SQL;

        $numberOfRow = (int) $this->getConnection()->fetchColumn($sql, [
            'project_id' => $project->getId()
        ]);

        $this->assertEquals(
            $numberOfRow,
            $expectedCount,
            sprintf('Invalid number of products for the project "%s"', $project->getCode())
        );
    }
}
