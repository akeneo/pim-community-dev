<?php

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2017 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace PimEnterprise\Bundle\ActivityManagerBundle\tests\integration\PreProcessing;

use Pim\Component\Catalog\Model\ProductInterface;
use PimEnterprise\Bundle\ActivityManagerBundle\tests\integration\ActivityManagerTestCase;
use PimEnterprise\Component\ActivityManager\Model\ProjectInterface;

class CompletenessPerAttributeGroupIntegration extends ActivityManagerTestCase
{
    /**
     * Test that the attribute group completeness per product/channel/locale are well calculated for a project
     * with the product 'tshirt-skyrim'
     *
     * 4 created projects with the product 'tshirt-skyrim':
     *     - ecommerce / en_US
     *     - ecommerce / fr_FR
     *     - mobile / en_US
     *     - mobile / fr_FR
     */
    public function testProjectCalculationOnTshirtSkyrim()
    {
        $productIdentifier = 'tshirt-skyrim';
        $projectFilters = [[
            'field'    => 'sku',
            'operator' => '=',
            'value'    => $productIdentifier,
        ]];

        $skyrimEcommerceEn = $this->saveProject('skyrim-ecommerce-en', 'en_US', 'Julia', 'ecommerce', $projectFilters);
        $skyrimEcommerceFr = $this->saveProject('skyrim-ecommerce-fr', 'fr_FR', 'Julia', 'ecommerce', $projectFilters);
        $skyrimMobileEn = $this->saveProject('skyrim-mobile-en', 'en_US', 'Julia', 'mobile', $projectFilters);
        $skyrimMobileFr = $this->saveProject('skyrim-mobile-fr', 'fr_FR', 'Julia', 'mobile', $projectFilters);

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

        $this->checkAttributeGroupCompleteness($skyrimMobileEn, $productIdentifier, [
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

        $this->checkAttributeGroupCompleteness($skyrimMobileFr, $productIdentifier, [
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

        $project = $this->saveProject('the-witcher-3-ecommerce-en', 'en_US', 'Julia', 'ecommerce', $projectFilters);
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
     * For that test all value should be empties, the "other" attribute group completeness should be "to do"
     *
     * Note: "other" has not a filled property for en_US and ecommerce but it has for mobile and fr_FR
     *
     * 1 created project with the product 'empty-technical-product':
     *     - Channel: ecommerce
     *     - Locale: en_US
     */
    public function testProjectCalculationWhenTheProductPropertiesAreEmpties()
    {
        $productIdentifier = 'empty-technical-product';
        $project = $this->saveProject('test-empty-property', 'en_US', 'Julia', 'ecommerce', [[
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
     * For that test all value should be empties, the "other" attribute group completeness should be "done"
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
        $project = $this->saveProject('test-full-property', 'en_US', 'Julia', 'ecommerce', [[
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
//        $sql = <<<SQL
//SELECT `has_at_least_one_required_attribute_filled`, `is_complete`
//FROM `pimee_activity_manager_completeness_per_attribute_group`
//WHERE `attribute_group_id` = :attribute_group_id
//AND product_id = :product_id;
//SQL;

        $productId = $this->get('pim_catalog.repository.product')->findOneByIdentifier($productIdentifier)->getId();

        foreach ($expectedAttributeGroupCompleteness as $group => $expectedCompleteness) {
            $attributeGroupId = $this->get('pim_catalog.repository.attribute_group')
                ->findOneByIdentifier($group)
                ->getId();

            $actualCompleteness = $this->getConnection()->fetchAssoc(
<<<SQL
SELECT `cag`.`has_at_least_one_required_attribute_filled`, `cag`.`is_complete`
FROM `pimee_activity_manager_project` AS `p`
INNER JOIN `pimee_activity_manager_project_product` AS `pp`
	ON `pp`.`project_id` = `p`.`id`
INNER JOIN `pimee_activity_manager_completeness_per_attribute_group` AS `cag`
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
     * A attribute group completeness is only calculated if the one of those attributes
     * are filled and require by the family.
     *
     * @param ProjectInterface $project
     * @param int              $expectedCount
     */
    private function checkTheNumberOfAttributeGroupCompleteness(
        ProjectInterface $project,
        $expectedCount
    ) {
//        $sql = <<<SQL
//SELECT COUNT(*)
//FROM `pimee_activity_manager_completeness_per_attribute_group`
//WHERE `product_id` = :product_id
//AND `channel_id` = :channel_id
//AND `locale_id` = :locale_id
//SQL;

        $numberOfRow = (int) $this->getConnection()->fetchColumn(
<<<SQL
SELECT COUNT(*)
FROM `pimee_activity_manager_project` AS `p`
INNER JOIN `pimee_activity_manager_project_product` AS `pp`
	ON `pp`.`project_id` = `p`.`id`
INNER JOIN `pimee_activity_manager_completeness_per_attribute_group` AS `cag`
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
