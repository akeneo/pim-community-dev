<?php

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2017 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace PimEnterprise\Bundle\TeamworkAssistantBundle\tests\integration\PreProcessing;

use Pim\Component\Catalog\Model\ProductInterface;
use PimEnterprise\Bundle\TeamworkAssistantBundle\tests\integration\TeamworkAssistantTestCase;
use PimEnterprise\Component\TeamworkAssistant\Model\ProjectInterface;
use PimEnterprise\Bundle\TeamWorkAssistantBundle\tests\integration\ActivityManagerTestCase;
use PimEnterprise\Component\TeamWorkAssistant\Model\ProjectInterface;
use PimEnterprise\Component\Security\Attributes;

class CompletenessPerAttributeGroupIntegration extends TeamworkAssistantTestCase
{
    /**
     * Test that the attribute group completeness per product/channel/locale are well calculated for a project
     * with the product 'tshirt-skyrim'
     *
     * 4 created projects with the product 'tshirt-skyrim':
     *     - ecommerce / en_US
     *     - ecommerce / fr_FR
     *     - tablet / en_US
     *     - tablet / fr_FR
     */
    public function testProjectCalculationOnTshirtSkyrim()
    {
        $productIdentifier = 'tshirt-skyrim';
        $projectFilters = [[
            'field'    => 'sku',
            'operator' => '=',
            'value'    => $productIdentifier,
        ]];

        $skyrimEcommerceEn = $this->createProject(
            'skyrim-ecommerce-en',
            'Julia',
            'en_US',
            'ecommerce',
            $projectFilters
        );
        $skyrimEcommerceFr = $this->createProject(
            'skyrim-ecommerce-fr',
            'Julia',
            'fr_FR',
            'ecommerce',
            $projectFilters
        );
        $skyrimTabletEn = $this->createProject('skyrim-tablet-en', 'Julia', 'en_US', 'tablet', $projectFilters);
        $skyrimTabletFr = $this->createProject('skyrim-tablet-fr', 'Julia', 'fr_FR', 'tablet', $projectFilters);

        $this->checkAttributeGroupCompleteness($skyrimEcommerceEn, $productIdentifier, [
            'general' => [
                'has_at_least_one_required_attribute_filled' => '0',
                'is_complete'                                => '1'
            ],
            'marketing' => [
                'has_at_least_one_required_attribute_filled' => '1',
                'is_complete'                                => '0'
            ],
            'technical' => [
                'has_at_least_one_required_attribute_filled' => '0',
                'is_complete'                                => '1'
            ],
        ]);

        $this->checkAttributeGroupCompleteness($skyrimEcommerceFr, $productIdentifier, [
            'general' => [
                'has_at_least_one_required_attribute_filled' => '0',
                'is_complete'                                => '1'
            ],
            'marketing' => [
                'has_at_least_one_required_attribute_filled' => '1',
                'is_complete'                                => '0'
            ],
            'technical' => [
                'has_at_least_one_required_attribute_filled' => '0',
                'is_complete'                                => '0'
            ],
        ]);

        $this->checkAttributeGroupCompleteness($skyrimTabletEn, $productIdentifier, [
            'general' => [
                'has_at_least_one_required_attribute_filled' => '0',
                'is_complete'                                => '1'
            ],
            'marketing' => [
                'has_at_least_one_required_attribute_filled' => '0',
                'is_complete'                                => '1'
            ],
            'technical' => [
                'has_at_least_one_required_attribute_filled' => '0',
                'is_complete'                                => '1'
            ],
        ]);

        $this->checkAttributeGroupCompleteness($skyrimTabletFr, $productIdentifier, [
            'general' => [
                'has_at_least_one_required_attribute_filled' => '0',
                'is_complete'                                => '1'
            ],
            'marketing' => [
                'has_at_least_one_required_attribute_filled' => '1',
                'is_complete'                                => '0'
            ],
            'technical' => [
                'has_at_least_one_required_attribute_filled' => '0',
                'is_complete'                                => '0'
            ],
        ]);
    }

    /**
     * When you recalculate a project it must have the same number of attribute group completeness
     *
     * 1 created project with the product 'tshirt-the-witcher-3':
     *     - Channel: ecommerce
     *     - Locale: en_US
     */
    public function testThatTheProjectRecalculationDoesNotAddAttributeGroupCompleteness()
    {
        $productIdentifier = 'tshirt-the-witcher-3';
        $projectFilters = [[
            'field'    => 'sku',
            'operator' => '=',
            'value'    => $productIdentifier,
        ]];

        $project = $this->createProject('the-witcher-3-ecommerce-en', 'Julia', 'en_US', 'ecommerce', $projectFilters);
        $this->checkAttributeGroupCompleteness($project, $productIdentifier, [
            'general' => [
                'has_at_least_one_required_attribute_filled' => '0',
                'is_complete'                                => '1'
            ],
            'marketing' => [
                'has_at_least_one_required_attribute_filled' => '0',
                'is_complete'                                => '1'
            ],
            'technical' => [
                'has_at_least_one_required_attribute_filled' => '0',
                'is_complete'                                => '0'
            ],
        ]);

        $this->calculateProject($project);
        $this->checkAttributeGroupCompleteness($project, $productIdentifier, [
            'general' => [
                'has_at_least_one_required_attribute_filled' => '0',
                'is_complete'                                => '1'
            ],
            'marketing' => [
                'has_at_least_one_required_attribute_filled' => '0',
                'is_complete'                                => '1'
            ],
            'technical' => [
                'has_at_least_one_required_attribute_filled' => '0',
                'is_complete'                                => '0'
            ],
        ]);
    }

    /**
     * Check that we get the value of product properties on the right channel, locale and we are able to get the
     * value for every attributes
     *
     * For that test all values should be empty, the "other" attribute group completeness should be "to do"
     *
     * Note: "other" has not a filled property for en_US and ecommerce but it has for tablet and fr_FR
     *
     * 1 created project with the product 'empty-technical-product':
     *     - Channel: ecommerce
     *     - Locale: en_US
     */
    public function testProjectCalculationWhenTheProductPropertiesAreEmpties()
    {
        $productIdentifier = 'empty-technical-product';
        $project = $this->createProject('test-empty-property', 'Julia', 'en_US', 'ecommerce', [[
            'field'    => 'sku',
            'operator' => '=',
            'value'    => $productIdentifier,
        ]]);

        $this->checkAttributeGroupCompleteness($project, $productIdentifier, [
            'general' => [
                'has_at_least_one_required_attribute_filled' => '0',
                'is_complete'                                => '1'
            ],
            'other' => [
                'has_at_least_one_required_attribute_filled' => '0',
                'is_complete'                                => '0'
            ]
        ]);
    }

    /**
     * For that test all values should be empty, the "other" attribute group completeness should be "done"
     *
     * Note: We need to insert reference data (color and fabric) to check that "other" is complete
     *
     * 1 created project with the product 'full-technical-product':
     *     - Channel: ecommerce
     *     - Locale: en_US
     *
     * TODO : Check asset collection.
     */
    public function testProjectCalculationWhenTheProductPropertiesAreFull()
    {
        /**
         * Load reference data in database
         */
        $this->getConnection()->insert('acme_reference_data_color', [
            'code'          => 'red',
            'name'          => 'red',
            'hex'           => '#FF0000',
            'red'           => 1,
            'green'         => 1,
            'blue'          => 1,
            'hue'           => 1,
            'hslSaturation' => 1,
            'light'         => 1,
            'hsvSaturation' => 1,
            'value'         => 1,
            'sortOrder'     => 10
        ]);

        $this->getConnection()->insert('acme_reference_data_fabric', [
            'code'      => 'latex',
            'name'      => 'Latex',
            'sortOrder' => 10
        ]);

        /**
         * Set a value to simple and multiple reference data properties
         */
        $productIdentifier = 'full-technical-product';
        $product = $this->get('pim_catalog.repository.product')->findOneByIdentifier($productIdentifier);
        $this->get('pim_catalog.updater.product')->update($product, [
            'values' => [
                'simple_reference_data_attribute' => [[
                    'locale' => null,
                    'scope'  => null,
                    'data'   => 'red',
                ]],
                'multi_reference_data_attribute' => [[
                    'locale' => null,
                    'scope'  => null,
                    'data'   => ['latex'],
                ]]
            ]
        ]);
        $this->get('pim_catalog.saver.product')->save($product);

        /**
         * Check the project completeness
         */
        $project = $this->createProject('test-full-property', 'Julia', 'en_US', 'ecommerce', [[
            'field'    => 'sku',
            'operator' => '=',
            'value'    => $productIdentifier,
        ]]);

        $this->checkAttributeGroupCompleteness($project, $productIdentifier, [
            'general' => [
                'has_at_least_one_required_attribute_filled' => '0',
                'is_complete'                                => '1'
            ],
            'other' => [
                'has_at_least_one_required_attribute_filled' => '0',
                'is_complete'                                => '1'
            ]
        ]);
    }

    public function testTheAllPermissionCategory()
    {
        $category = $this->get('pim_catalog.repository.category')->findOneByIdentifier('car');
        $userGroup = $this->get('pim_user.repository.group')->findOneByIdentifier('All');
        $this->get('pimee_security.manager.category_access')
            ->grantAccess($category, $userGroup, Attributes::OWN_PRODUCTS);

        $productIdentifier = 'technical-product';
        $product = $this->get('pim_catalog.repository.product')->findOneByIdentifier($productIdentifier);

        $project = $this->createProject('test-categ', 'Julia', 'en_US', 'ecommerce', [[
            'field'    => 'sku',
            'operator' => '=',
            'value'    => $productIdentifier,
        ]]);

        $catalogManager = $this->get('pim_user.repository.group')->findOneByIdentifier('Catalog Manager');
        $marketing = $this->get('pim_user.repository.group')->findOneByIdentifier('Marketing');
        $result = $this->get('pimee_activity_manager.calculator.contributor_group')->calculate($project, $product);

        $this->assertSame($result, [$catalogManager, $marketing]);
    }

    /**
     * Check that the attribute group completeness is well calculated for a product that belong to a project.
     *
     * @param ProjectInterface $project
     * @param string           $productIdentifier
     * @param array            $expectedAttributeGroupCompleteness
     */
    private function checkAttributeGroupCompleteness(
        ProjectInterface $project,
        $productIdentifier,
        array $expectedAttributeGroupCompleteness
    ) {
        $this->checkAttributeGroupCompletenessData($project, $productIdentifier, $expectedAttributeGroupCompleteness);
        $this->checkTheNumberOfAttributeGroupCompleteness($project, count($expectedAttributeGroupCompleteness));
    }

    /**
     * Check that the attribute group completeness for product/project locale/project channel is well calculated
     *
     * @param ProjectInterface $project
     * @param string           $productIdentifier
     * @param array            $expectedAttributeGroupCompleteness
     */
    private function checkAttributeGroupCompletenessData(
        ProjectInterface $project,
        $productIdentifier,
        array $expectedAttributeGroupCompleteness
    ) {
        $productId = $this->get('pim_catalog.repository.product')->findOneByIdentifier($productIdentifier)->getId();

        foreach ($expectedAttributeGroupCompleteness as $group => $expectedCompleteness) {
            $attributeGroupId = $this->get('pim_catalog.repository.attribute_group')
                ->findOneByIdentifier($group)
                ->getId();

            $actualCompleteness = $this->getConnection()->fetchAssoc(
<<<SQL
SELECT `cag`.`has_at_least_one_required_attribute_filled`, `cag`.`is_complete`
FROM `pimee_teamwork_assistant_project` AS `p`
INNER JOIN `pimee_teamwork_assistant_project_product` AS `pp`
	ON `pp`.`project_id` = `p`.`id`
INNER JOIN `pimee_teamwork_assistant_completeness_per_attribute_group` AS `cag`
	ON `pp`.`product_id` = `cag`.`product_id` AND `p`.`channel_id` = `cag`.`channel_id` AND `p`.`locale_id` = `cag`.`locale_id`
WHERE `p`.`id` = :project_id
AND `cag`.`attribute_group_id` = :attribute_group_id
AND `cag`.`product_id` = :product_id
SQL
                ,
                [
                    'attribute_group_id' => $attributeGroupId,
                    'project_id'         => $project->getId(),
                    'product_id'         => $productId,
                ]
            );

            $this->assertSame(
                $actualCompleteness,
                $expectedCompleteness,
                sprintf('Attribute group completeness is not valid for the attribute group %s', $group)
            );
        }
    }

    /**
     * Checks that the calculated number of attribute group completeness is correct.
     *
     * An attribute group completeness is only calculated if one of those attributes
     * are filled and required by the family.
     *
     * @param ProjectInterface $project
     * @param int              $expectedCount
     */
    private function checkTheNumberOfAttributeGroupCompleteness(
        ProjectInterface $project,
        $expectedCount
    ) {
        $numberOfRow = (int) $this->getConnection()->fetchColumn(
<<<SQL
SELECT COUNT(*)
FROM `pimee_teamwork_assistant_project` AS `p`
INNER JOIN `pimee_teamwork_assistant_project_product` AS `pp`
	ON `pp`.`project_id` = `p`.`id`
INNER JOIN `pimee_teamwork_assistant_completeness_per_attribute_group` AS `cag`
	ON `pp`.`product_id` = `cag`.`product_id` AND `p`.`channel_id` = `cag`.`channel_id` AND `p`.`locale_id` = `cag`.`locale_id`
WHERE `p`.`id` = :project_id
SQL
            ,
            [
                'project_id' => $project->getId(),
            ]
        );

        $this->assertSame(
            $numberOfRow,
            $expectedCount,
            sprintf('Invalid number of calculated attribute group completeness for the project %s', $project->getCode())
        );
    }
}
