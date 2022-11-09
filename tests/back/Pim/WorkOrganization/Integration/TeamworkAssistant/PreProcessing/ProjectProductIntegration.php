<?php

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2017 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace AkeneoTestEnterprise\Pim\WorkOrganization\Integration\TeamworkAssistant\PreProcessing;

use AkeneoTestEnterprise\Pim\WorkOrganization\Integration\TeamworkAssistant\TeamworkAssistantTestCase;
use Akeneo\Pim\WorkOrganization\TeamworkAssistant\Component\Model\ProjectInterface;

class ProjectProductIntegration extends TeamworkAssistantTestCase
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
        $project = $this->createProject('test-thsirt', 'Julia', 'en_US', 'ecommerce', [
            [
                'field'    => 'family',
                'operator' => 'IN',
                'value'    => ['tshirt'],
            ],
        ]);

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
        $project = $this->createProject('test-technical-family', 'Julia', 'en_US', 'ecommerce', [
            [
                'field'    => 'family',
                'operator' => 'IN',
                'value'    => ['technical_family'],
            ],
        ]);

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
        $project = $this->createProject('test-project-creator-right', 'Teddy', 'en_US', 'ecommerce', [
            [
                'field'    => 'family',
                'operator' => 'IN',
                'value'    => ['tshirt'],
            ],
        ]);

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
FROM `pimee_teamwork_assistant_project_product`
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
