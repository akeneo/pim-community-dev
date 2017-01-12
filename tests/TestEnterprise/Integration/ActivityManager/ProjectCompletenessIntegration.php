<?php

namespace Akeneo\TestEnterprise\Integration\ActivityManager;

use PimEnterprise\Component\ActivityManager\Model\ProjectCompleteness;
use PimEnterprise\Component\ActivityManager\Model\ProjectInterface;

class ProjectCompletenessIntegration extends ActivityManagerTestCase
{
    /** @var ProjectInterface */
    protected static $project;

    public function testCreateAProjectOnTheTshirtFamily()
    {
        $this::$project = $this->createProject([
            'label' => 'Tshirt',
            'locale' => 'en_US',
            'owner'=> 'julia',
            'channel' => 'ecommerce',
            'product_filters' =>[
                [
                    'field' => 'family',
                    'operator' => 'IN',
                    'value' => ['tshirt'],
                    'context' => ['locale' => 'en_US', 'scope' => 'ecommerce'],
                ],
            ],
        ]);

        $this->calculateProject($this::$project);
    }

    /**
     * Julia is a project create, she creates a project on the family tshirt (3 products)
     * She can access to all categories and attributes groups (for all product at least one attribute group is not done)
     */
    public function testTheTshirtProjectCompletenessForTheProjectCreate()
    {
        $projectCompleteness = $this->getProjectCompleteness($this::$project);
        $this->checkProductSelectionCount($projectCompleteness, 3);
        $this->checkProjectCompleteness($projectCompleteness, 0, 3, 0);
    }

    /**
     * Mary is a project contributor, she can edit the marking attribute group
     */
    public function testTheTshirtProjectCompletenessForMary()
    {
        $projectCompleteness = $this->getProjectCompleteness($this::$project, 'Mary');

        $this->checkProductSelectionCount($projectCompleteness, 3);
        $this->checkProjectCompleteness($projectCompleteness, 1, 1, 1);
    }

    /**
     * Peter is administrator, he does not enrich product but he can see the products
     */
    public function testTheTshirtProjectCompletenessForPeter()
    {
        $projectCompleteness = $this->getProjectCompleteness($this::$project, 'Peter');
        $this->checkProductSelectionCount($projectCompleteness, 0);
    }

    /**
     * Katy is media manager, she can edit Clothing category but the family does not have any media
     */
    public function testTheTshirtProjectCompletenessForKathy()
    {
        $projectCompleteness = $this->getProjectCompleteness($this::$project, 'Kathy');
        $this->checkProductSelectionCount($projectCompleteness, 0);
    }

    /**
     * Teddy is technical "High-Tech" contributor, he can not see the the clothing category
     */
    public function testTheTshirtProjectCompletenessForTeddy()
    {
        $projectCompleteness = $this->getProjectCompleteness($this::$project, 'Kathy');
        $this->checkProductSelectionCount($projectCompleteness, 0);
    }

    /**
     * Claude
     *      - is technical contributor (technical clothing attribute group),
     *      - can access to "Clothing" category
     * The property "material" is only filled for one product for the ecommerce channel.
     */
    public function testTheTshirtProjectCompletenessForClaude()
    {
        $projectCompleteness = $this->getProjectCompleteness($this::$project, 'Claude');

        $this->checkProductSelectionCount($projectCompleteness, 3);
        $this->checkProjectCompleteness($projectCompleteness, 2, 0, 1);
    }

    /**
     * Marc
     *      - is technical contributor (technical clothing attribute group),
     *      - can access to "Clothing" and "High Tech" category
     * The property "material" is only filled for one product for the ecommerce channel.
     */
    public function testTheTshirtProjectCompletenessForMarc()
    {
        $projectCompleteness = $this->getProjectCompleteness($this::$project, 'Marc');

        $this->checkProductSelectionCount($projectCompleteness, 3);
        $this->checkProjectCompleteness($projectCompleteness, 2, 0, 1);
    }

    public function testCreateAProjectOnTheTshirtAndUsbKeysFamily()
    {
        $this::$project = $this->createProject([
            'label' => 'Tshirt & USB keys',
            'locale' => 'en_US',
            'owner'=> 'julia',
            'channel' => 'ecommerce',
            'product_filters' =>[
                [
                    'field' => 'family',
                    'operator' => 'IN',
                    'value' => ['tshirt', 'usb_keys'],
                    'context' => ['locale' => 'en_US', 'scope' => 'ecommerce'],
                ],
            ],
        ]);

        $this->calculateProject($this::$project);
    }

    /**
     * Julia is a project create, she creates a project on the family tshirt (3 products)
     * She can access to all categories and attributes groups (for all product at least one attribute group is not done)
     */
    public function testTheTshirtAndUsbKeysProjectCompletenessForTheProjectCreate()
    {
        $projectCompleteness = $this->getProjectCompleteness($this::$project);
        $this->checkProductSelectionCount($projectCompleteness, 5);
        $this->checkProjectCompleteness($projectCompleteness, 0, 5, 0);
    }

    /**
     * Marc
     *      - is technical contributor (technical clothing attribute group),
     *      - can access to "Clothing" and "High Tech" category
     * The property "material" is only filled for one product for the ecommerce channel.
     */
    public function testTshirtAndUsbKeysProjectCompletenessForMarc()
    {
        $projectCompleteness = $this->getProjectCompleteness($this::$project, 'Marc');

        $this->checkProductSelectionCount($projectCompleteness, 5);
        $this->checkProjectCompleteness($projectCompleteness, 2, 0, 3);
    }

    /**
     * Mary is a project contributor, she can edit the marking attribute group
     */
    public function testTshirtAndUsbKeysProjectCompletenessForMary()
    {
        $projectCompleteness = $this->getProjectCompleteness($this::$project, 'Mary');

        $this->checkProductSelectionCount($projectCompleteness, 5);
        $this->checkProjectCompleteness($projectCompleteness, 2, 2, 1);
    }

    /**
     * Check that the number of product which are done, in progress or to do
     *
     * @param ProjectCompleteness $projectCompleteness
     * @param int                 $expectedTodo
     * @param int                 $expectedInProgress
     * @param int                 $expectedDone
     */
    private function checkProjectCompleteness(
        ProjectCompleteness $projectCompleteness,
        $expectedTodo,
        $expectedInProgress,
        $expectedDone
    ) {
        $this->assertEquals(
            $projectCompleteness->getProductsCountDone(),
            $expectedDone,
            'Product count done are invalid'
        );

        $this->assertEquals(
            $projectCompleteness->getProductsCountInProgress(),
            $expectedInProgress,
            'Product count in progress are invalid'
        );

        $this->assertEquals(
            $projectCompleteness->getProductsCountTodo(),
            $expectedTodo,
            'Product count to do are invalid'
        );
    }

    /**
     * Check the number of products which are editable by the user
     *
     * @param ProjectCompleteness $projectCompleteness
     * @param int                 $expectedCount
     */
    private function checkProductSelectionCount(ProjectCompleteness $projectCompleteness, $expectedCount)
    {
        $this->assertEquals(
            $expectedCount,
            $projectCompleteness->getProductsCountDone() +
            $projectCompleteness->getProductsCountInProgress() +
            $projectCompleteness->getProductsCountTodo(),
            sprintf('The user can edit/see %d product(s) his/her project.', $expectedCount)
        );
    }
}
