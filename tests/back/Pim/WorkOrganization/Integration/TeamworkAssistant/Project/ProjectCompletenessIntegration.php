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

use Akeneo\Pim\WorkOrganization\TeamworkAssistant\Bundle\Datagrid\Filter\ProjectCompletenessFilter;
use Akeneo\Pim\WorkOrganization\TeamworkAssistant\Component\Model\ProjectCompleteness;
use Akeneo\Pim\WorkOrganization\TeamworkAssistant\Component\Model\ProjectInterface;
use AkeneoTestEnterprise\Pim\WorkOrganization\Integration\TeamworkAssistant\TeamworkAssistantTestCase;

class ProjectCompletenessIntegration extends TeamworkAssistantTestCase
{
    /**
     * Family: tshirt (3 products + 1 uncategorized product)
     *
     * Channel: ecommerce
     * Locale: en_US
     *
     * @group critical
     */
    public function testCreateAProjectOnTheTshirtFamily()
    {
        $this->createProject('project', 'Julia', 'en_US', 'ecommerce', [
            [
                'field'    => 'family',
                'operator' => 'IN',
                'value'    => ['usb_keys'],
            ],
        ]);

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
        $this->checkProjectCompletenessFilterForOwner($project, $projectCompleteness, 'Julia');

        /**
         * Mary is a project contributor, she can edit the marking attribute group
         */
        $projectCompleteness = $this->getProjectCompleteness($project, 'Mary');

        $this->checkProductSelectionCount($projectCompleteness, 4, 'Mary');
        $this->checkProjectCompleteness($projectCompleteness, 2, 1, 1, 'Mary');
        $this->checkProjectCompletenessFilterForContributor($project, $projectCompleteness, 'Mary');

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
        $this->checkProjectCompletenessFilterForContributor($project, $projectCompleteness, 'Teddy');

        /**
         * Claude
         *      - is technical contributor (technical clothing attribute group),
         *      - can access to "Clothing" category
         */
        $projectCompleteness = $this->getProjectCompleteness($project, 'Claude');

        $this->checkProductSelectionCount($projectCompleteness, 4, 'Claude');
        $this->checkProjectCompleteness($projectCompleteness, 3, 0, 1, 'Claude');
        $this->checkProjectCompletenessFilterForContributor($project, $projectCompleteness, 'Claude');

        /**
         * Marc
         *      - is technical contributor (technical clothing attribute group),
         *      - can access to "Clothing" and "High Tech" category
         */
        $projectCompleteness = $this->getProjectCompleteness($project, 'Marc');

        $this->checkProductSelectionCount($projectCompleteness, 4, 'Marc');
        $this->checkProjectCompleteness($projectCompleteness, 3, 0, 1, 'Marc');
        $this->checkProjectCompletenessFilterForContributor($project, $projectCompleteness, 'Marc');
    }

    /**
     * Family: tshirt(3 products + 1 uncategorized product) and usb_keys (2 products)
     * Note: The hight tech and clothing category share a common product
     *
     * Channel: ecommerce
     * Locale: en_US
     *
     * @group critical
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
        $this->checkProjectCompletenessFilterForOwner($project, $projectCompleteness, 'Julia');

        /**
         * Marc
         *      - is technical contributor (technical clothing attribute gcroup),
         *      - can access to "Clothing" and "High Tech" category
         */
        $projectCompleteness = $this->getProjectCompleteness($project, 'Marc');

        $this->checkProductSelectionCount($projectCompleteness, 6, 'Marc');
        $this->checkProjectCompleteness($projectCompleteness, 3, 0, 3, 'Marc');
        $this->checkProjectCompletenessFilterForContributor($project, $projectCompleteness, 'Marc');

        /**
         * Mary is a project contributor, she can edit the marking attribute group
         */

        $projectCompleteness = $this->getProjectCompleteness($project, 'Mary');

        $this->checkProductSelectionCount($projectCompleteness, 6, 'Mary');
        $this->checkProjectCompleteness($projectCompleteness, 3, 2, 1, 'Mary');
        $this->checkProjectCompletenessFilterForContributor($project, $projectCompleteness, 'Mary');

        /**
         * Claude
         *      - is technical contributor (technical clothing attribute group),
         *      - can access to "Clothing" category
         */
        $projectCompleteness = $this->getProjectCompleteness($project, 'Claude');

        $this->checkProductSelectionCount($projectCompleteness, 4, 'Claude');
        $this->checkProjectCompleteness($projectCompleteness, 3, 0, 1, 'Claude');
        $this->checkProjectCompletenessFilterForContributor($project, $projectCompleteness, 'Claude');
    }

    /**
     * Check that the project completeness is computed with the right locale and the right channel.
     *
     * Family: tshirt (3 products + 1 uncategorized product)
     * Channel: tablet
     * Locale: fr_FR
     *
     * @group critical
     */
    public function testCreateAProjectOnTheTshirtFamilyButWithAnotherChannel()
    {
        $project = $this->createProject('Tshirt - print', 'Julia', 'fr_FR', 'tablet', [
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
        $this->checkProjectCompletenessFilterForOwner($project, $projectCompleteness, 'Julia');

        /**
         * Mary is a project contributor, she can edit the marking attribute group
         */
        $projectCompleteness = $this->getProjectCompleteness($project, 'Mary');

        $this->checkProductSelectionCount($projectCompleteness, 4, 'Mary');
        $this->checkProjectCompleteness($projectCompleteness, 0, 3, 1, 'Mary');
        $this->checkProjectCompletenessFilterForContributor($project, $projectCompleteness, 'Mary');

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
        $this->checkProjectCompletenessFilterForContributor($project, $projectCompleteness, 'Teddy');

        /**
         * Claude
         *      - is technical contributor (technical clothing attribute group),
         *      - can access to "Clothing" category
         */
        $projectCompleteness = $this->getProjectCompleteness($project, 'Claude');

        $this->checkProductSelectionCount($projectCompleteness, 4, 'Claude');
        $this->checkProjectCompleteness($projectCompleteness, 2, 0, 2, 'Claude');
        $this->checkProjectCompletenessFilterForContributor($project, $projectCompleteness, 'Claude');

        /**
         * Marc
         *      - is technical contributor (technical clothing attribute group),
         *      - can access to "Clothing" and "High Tech" category
         */
        $projectCompleteness = $this->getProjectCompleteness($project, 'Marc');

        $this->checkProductSelectionCount($projectCompleteness, 4, 'Marc');
        $this->checkProjectCompleteness($projectCompleteness, 2, 0, 2, 'Marc');
        $this->checkProjectCompletenessFilterForContributor($project, $projectCompleteness, 'Marc');
    }


    /**
     * Check that the project completeness is computed depending on the locale accesses.
     *
     * Family: tshirt (3 products + 1 uncategorized product)
     * Channel: tablet
     * Locale: en_ES
     *
     * @group critical
     */
    public function testCreateAProjectOnTheTshirtFamilyButWithAnotherChannelAndLocale()
    {
        $project = $this->createProject('Tshirt - print', 'Julia', 'es_ES', 'tablet', [
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
        $this->checkProjectCompletenessFilterForOwner($project, $projectCompleteness, 'Julia');

        /**
         * Mary is a project contributor, she can edit the marking attribute group
         */
        $projectCompleteness = $this->getProjectCompleteness($project, 'Mary');

        $this->checkProductSelectionCount($projectCompleteness, 4, 'Mary');
        $this->checkProjectCompleteness($projectCompleteness, 4, 0, 0, 'Mary');
        $this->checkProjectCompletenessFilterForContributor($project, $projectCompleteness, 'Mary');

        /**
         * Peter is administrator, he does not enrich product but he can see products
         */
        $projectCompleteness = $this->getProjectCompleteness($project, 'Peter');

        $this->checkProductSelectionCount($projectCompleteness, 0, 'Peter');

        /**
         * Katy
         *      - is media manager
         *      - she can edit products in the "Clothing" category but they don't not have any media property
         *      - cannot access to the es_ES locale
         */
        $projectCompleteness = $this->getProjectCompleteness($project, 'Katy');

        $this->checkProductSelectionCount($projectCompleteness, 0, 'Katy');

        /**
         * Teddy
         *      - is technical "High-Tech" contributor
         *      - he cannot see the clothing category
         *      - cannot access to the es_ES locale
         */
        $projectCompleteness = $this->getProjectCompleteness($project, 'Teddy');

        $this->checkProductSelectionCount($projectCompleteness, 0, 'Teddy');

        /**
         * Claude
         *      - is technical contributor (technical clothing attribute group),
         *      - can access to "Clothing" category
         *      - cannot access to the es_ES locale
         */
        $projectCompleteness = $this->getProjectCompleteness($project, 'Claude');

        $this->checkProductSelectionCount($projectCompleteness, 0, 'Claude');

        /**
         * Marc
         *      - is technical contributor (technical clothing attribute group),
         *      - can access to "Clothing" and "High Tech" category
         *      - cannot access to the es_ES locale
         */
        $projectCompleteness = $this->getProjectCompleteness($project, 'Marc');

        $this->checkProductSelectionCount($projectCompleteness, 0, 'Marc');
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
            sprintf('Product count to do is invalid for %s', $username)
        );

        $this->assertEquals(
            $expectedInProgress,
            $projectCompleteness->getProductsCountInProgress(),
            sprintf('Product count in progress is invalid for %s', $username)
        );

        $this->assertEquals(
            $expectedDone,
            $projectCompleteness->getProductsCountDone(),
            sprintf('Product count done is invalid for %s', $username)
        );
    }

    /**
     * Check the number of products returned done, in progress and to do, by using completeness filter
     *
     * @param ProjectInterface    $project
     * @param ProjectCompleteness $projectCompleteness
     * @param string              $username
     */
    private function checkProjectCompletenessFilterForOwner(
        ProjectInterface $project,
        ProjectCompleteness $projectCompleteness,
        string $username
    ) {
        $repository = $this->get('pimee_teamwork_assistant.repository.project_completeness');
        $todo = count($repository->findProductIdentifiers($project, ProjectCompletenessFilter::OWNER_TODO, $username));
        $inProgress = count($repository->findProductIdentifiers($project, ProjectCompletenessFilter::OWNER_IN_PROGRESS, $username));
        $done = count($repository->findProductIdentifiers($project, ProjectCompletenessFilter::OWNER_DONE, $username));

        $this->assertEquals(
            $projectCompleteness->getProductsCountTodo(),
            $todo,
            sprintf('Product count with filter to do is invalid for %s', $username)
        );

        $this->assertEquals(
            $projectCompleteness->getProductsCountInProgress(),
            $inProgress,
            sprintf('Product count with filter progress is invalid for %s', $username)
        );

        $this->assertEquals(
            $projectCompleteness->getProductsCountDone(),
            $done,
            sprintf('Product count with filter done is invalid for %s', $username)
        );
    }

    /**
     * Check the number of products returned done, in progress and to do, by using completeness filter
     *
     * @param ProjectInterface    $project
     * @param ProjectCompleteness $projectCompleteness
     * @param string              $username
     */
    private function checkProjectCompletenessFilterForContributor(
        ProjectInterface $project,
        ProjectCompleteness $projectCompleteness,
        string $username
    ) {
        $repository = $this->get('pimee_teamwork_assistant.repository.project_completeness');
        $todo = count($repository->findProductIdentifiers($project, ProjectCompletenessFilter::CONTRIBUTOR_TODO, $username));
        $inProgress = count($repository->findProductIdentifiers($project, ProjectCompletenessFilter::CONTRIBUTOR_IN_PROGRESS, $username));
        $done = count($repository->findProductIdentifiers($project, ProjectCompletenessFilter::CONTRIBUTOR_DONE, $username));

        $this->assertEquals(
            $projectCompleteness->getProductsCountTodo(),
            $todo,
            sprintf('Product count with filter to do is invalid for %s', $username)
        );

        $this->assertEquals(
            $projectCompleteness->getProductsCountInProgress(),
            $inProgress,
            sprintf('Product count with filter progress is invalid for %s', $username)
        );

        $this->assertEquals(
            $projectCompleteness->getProductsCountDone(),
            $done,
            sprintf('Product count with filter done is invalid for %s', $username)
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
