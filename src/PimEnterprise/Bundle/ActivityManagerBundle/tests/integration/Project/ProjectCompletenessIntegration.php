<?php

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2017 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace PimEnterprise\Bundle\ActivityManagerBundle\tests\integration\Project;

use PimEnterprise\Bundle\ActivityManagerBundle\tests\integration\ActivityManagerTestCase;
use PimEnterprise\Component\ActivityManager\Model\ProjectCompleteness;

class ProjectCompletenessIntegration extends ActivityManagerTestCase
{
    /**
     * Family: tshirt (3 products + 1 uncategorized product)
     *
     * Channel: ecommerce
     * Locale: en_US
     */
    public function testCreateAProjectOnTheTshirtFamily()
    {
        $project = $this->createProject('Tshirt - ecommerce', 'Julia', 'en_US',  'ecommerce', [
            [
                'field'    => 'family',
                'operator' => 'IN',
                'value'    => ['tshirt'],
            ],
        ]);

        /**
         * Julia is a project creator, she creates a project on the "tshirt" family
         * She can access to all categories and attributes groups (for all products at least one attribute group is not done)
         */
        $projectCompleteness = $this->getProjectCompleteness($project);
        $this->checkProductSelectionCount($projectCompleteness, 4, 'Julia');
        $this->checkProjectCompleteness($projectCompleteness, 0, 4, 0, 'Julia');

        /**
         * Mary is a project contributor, she can edit the marking attribute group
         */
        $projectCompleteness = $this->getProjectCompleteness($project, 'Mary');

        $this->checkProductSelectionCount($projectCompleteness, 4, 'Mary');
        $this->checkProjectCompleteness($projectCompleteness, 2, 1, 1, 'Mary');

        /**
         * Peter is administrator, he does not enrich product but he can see products
         */
        $projectCompleteness = $this->getProjectCompleteness($project, 'Peter');

        $this->checkProductSelectionCount($projectCompleteness, 0, 'Peter');

        /**
         * Katy
         *      - is media manager
         *      - she can edit products in the "Clothing" category but they don't not have any media property
         */
        $projectCompleteness = $this->getProjectCompleteness($project, 'Katy');

        $this->checkProductSelectionCount($projectCompleteness, 0, 'Katy');

        /**
         * Teddy
         *      - is technical "High-Tech" contributor
         *      - he cannot see the clothing category
         */
        $projectCompleteness = $this->getProjectCompleteness($project, 'Teddy');

        $this->checkProductSelectionCount($projectCompleteness, 2, 'Teddy');
        $this->checkProjectCompleteness($projectCompleteness, 2, 0, 0, 'Teddy');

        /**
         * Claude
         *      - is technical contributor (technical clothing attribute group),
         *      - can access to "Clothing" category
         */
        $projectCompleteness = $this->getProjectCompleteness($project, 'Claude');

        $this->checkProductSelectionCount($projectCompleteness, 4, 'Claude');
        $this->checkProjectCompleteness($projectCompleteness, 3, 0, 1, 'Claude');

        /**
         * Marc
         *      - is technical contributor (technical clothing attribute group),
         *      - can access to "Clothing" and "High Tech" category
         */
        $projectCompleteness = $this->getProjectCompleteness($project, 'Marc');

        $this->checkProductSelectionCount($projectCompleteness, 4, 'Marc');
        $this->checkProjectCompleteness($projectCompleteness, 3, 0, 1, 'Marc');
    }

    /**
     * Family: tshirt(3 products + 1 uncategorized product) and usb_keys (2 products)
     * Note: The hight tech and clothing category share a common product
     *
     * Channel: ecommerce
     * Locale: en_US
     */
    public function testCreateAProjectOnTheTshirtAndUsbKeysFamily()
    {
        $project = $this->createProject('Tshirt & USB keys - ecommerce', 'Julia', 'en_US', 'ecommerce', [
            [
                'field'    => 'family',
                'operator' => 'IN',
                'value'    => ['tshirt', 'usb_keys'],
            ],
        ]);

        /**
         * Julia is a project creator, she creates a project on the "tshirt" family
         * She can access to all categories and attributes groups (for all product at least one attribute group is not done)
         */
        $projectCompleteness = $this->getProjectCompleteness($project);

        $this->checkProductSelectionCount($projectCompleteness, 6, 'Julia');
        $this->checkProjectCompleteness($projectCompleteness, 0, 6, 0, 'Julia');

        /**
         * Marc
         *      - is technical contributor (technical clothing attribute gcroup),
         *      - can access to "Clothing" and "High Tech" category
         */
        $projectCompleteness = $this->getProjectCompleteness($project, 'Marc');

        $this->checkProductSelectionCount($projectCompleteness, 6, 'Marc');
        $this->checkProjectCompleteness($projectCompleteness, 3, 0, 3, 'Marc');

        /**
         * Mary is a project contributor, she can edit the marking attribute group
         */

        $projectCompleteness = $this->getProjectCompleteness($project, 'Mary');

        $this->checkProductSelectionCount($projectCompleteness, 6, 'Mary');
        $this->checkProjectCompleteness($projectCompleteness, 3, 2, 1, 'Mary');

        /**
         * Claude
         *      - is technical contributor (technical clothing attribute group),
         *      - can access to "Clothing" category
         */
        $projectCompleteness = $this->getProjectCompleteness($project, 'Claude');

        $this->checkProductSelectionCount($projectCompleteness, 4, 'Claude');
        $this->checkProjectCompleteness($projectCompleteness, 3, 0, 1, 'Claude');
    }

    /**
     * Check that the complete is computed with the right locale and the right channel.
     *
     * Family: tshirt (3 products + 1 uncategorized product)
     * Channel: mobile
     * Locale: fr_FR
     */
    public function testCreateAProjectOnTheTshirtFamilyButWithAnotherChannel()
    {
        $project = $this->createProject('Tshirt - print', 'Julia', 'fr_FR', 'mobile', [
            [
                'field'    => 'family',
                'operator' => 'IN',
                'value'    => ['tshirt'],
                'context'  => ['locale' => 'fr_FR', 'scope' => 'mobile'],
            ],
        ]);

        $this->calculateProject($project);

        /**
         * Julia is a project creator, she creates a project on the "tshirt" family
         * She can access to all categories and attributes groups (for all products at least one attribute group is not done)
         */
        $projectCompleteness = $this->getProjectCompleteness($project);
        $this->checkProductSelectionCount($projectCompleteness, 4, 'Julia');
        $this->checkProjectCompleteness($projectCompleteness, 0, 4, 0, 'Julia');

        /**
         * Mary is a project contributor, she can edit the marking attribute group
         */
        $projectCompleteness = $this->getProjectCompleteness($project, 'Mary');

        $this->checkProductSelectionCount($projectCompleteness, 4, 'Mary');
        $this->checkProjectCompleteness($projectCompleteness, 0, 3, 1, 'Mary');

        /**
         * Peter is administrator, he does not enrich product but he can see products
         */
        $projectCompleteness = $this->getProjectCompleteness($project, 'Peter');

        $this->checkProductSelectionCount($projectCompleteness, 0, 'Peter');

        /**
         * Katy
         *      - is media manager
         *      - she can edit products in the "Clothing" category but they don't not have any media property
         */
        $projectCompleteness = $this->getProjectCompleteness($project, 'Katy');

        $this->checkProductSelectionCount($projectCompleteness, 0, 'Katy');

        /**
         * Teddy
         *      - is technical "High-Tech" contributor
         *      - he cannot see the clothing category
         */
        $projectCompleteness = $this->getProjectCompleteness($project, 'Teddy');

        $this->checkProductSelectionCount($projectCompleteness, 2, 'Teddy');
        $this->checkProjectCompleteness($projectCompleteness, 1, 0, 1, 'Teddy');

        /**
         * Claude
         *      - is technical contributor (technical clothing attribute group),
         *      - can access to "Clothing" category
         */
        $projectCompleteness = $this->getProjectCompleteness($project, 'Claude');

        $this->checkProductSelectionCount($projectCompleteness, 4, 'Claude');
        $this->checkProjectCompleteness($projectCompleteness, 2, 0, 2, 'Claude');

        /**
         * Marc
         *      - is technical contributor (technical clothing attribute group),
         *      - can access to "Clothing" and "High Tech" category
         */
        $projectCompleteness = $this->getProjectCompleteness($project, 'Marc');

        $this->checkProductSelectionCount($projectCompleteness, 4, 'Marc');
        $this->checkProjectCompleteness($projectCompleteness, 2, 0, 2, 'Marc');
    }

    /**
     * Check the number of products done, in progress or to do
     *
     * @param ProjectCompleteness $projectCompleteness
     * @param int                 $expectedTodo
     * @param int                 $expectedInProgress
     * @param int                 $expectedDone
     * @param string              $username
     */
    private function checkProjectCompleteness(
        ProjectCompleteness $projectCompleteness,
        $expectedTodo,
        $expectedInProgress,
        $expectedDone,
        $username
    ) {
        $this->assertEquals(
            $expectedTodo,
            $projectCompleteness->getProductsCountTodo(),
            sprintf('Product count to do are invalid for %s', $username)
        );

        $this->assertEquals(
            $expectedInProgress,
            $projectCompleteness->getProductsCountInProgress(),
            sprintf('Product count in progress are invalid for %s', $username)
        );

        $this->assertEquals(
            $expectedDone,
            $projectCompleteness->getProductsCountDone(),
            sprintf('Product count done are invalid for %s', $username)
        );
    }

    /**
     * Check the number of products editable by the user
     *
     * @param ProjectCompleteness $projectCompleteness
     * @param int                 $expectedCount
     * @param string              $username
     */
    private function checkProductSelectionCount(
        ProjectCompleteness $projectCompleteness,
        $expectedCount,
        $username
    ) {
        $this->assertEquals(
            $expectedCount,
            $projectCompleteness->getProductsCountDone() +
            $projectCompleteness->getProductsCountInProgress() +
            $projectCompleteness->getProductsCountTodo(),
            sprintf('%s must edit/see %d product(s) for his/her project.', $username, $expectedCount)
        );
    }
}
