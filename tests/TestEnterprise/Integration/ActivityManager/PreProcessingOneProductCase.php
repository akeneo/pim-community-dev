<?php

namespace TestEnterprise\Integration\ActivityManager;

use Pim\Component\Catalog\Model\ProductInterface;
use PimEnterprise\Component\ActivityManager\Model\ProjectInterface;

class PreProcessingOneProductCase extends ActivityManagerTestCase
{
    /** @var ProjectInterface */
    private static $project;

    /**
     * Create a project with only one product to test that the pre processed data are well calculated
     *
     * Product: tshirt-the-witcher-3
     * Channel: ecommerce
     * Locale: en_US
     */
    public function testProjectCreation()
    {
        $this::$project = $this->createProject([
            'label' => 'test-project',
            'locale' => 'en_US',
            'owner'=> 'admin',
            'channel' => 'ecommerce',
            'product_filters' =>[
                [
                    'field' => 'sku',
                    'operator' => '=',
                    'value' => 'tshirt-the-witcher-3',
                    'context' => ['locale' => 'en_US', 'scope' => 'ecommerce'],
                ],
            ],
        ]);

        $product = $this->get('pim_catalog.repository.product')->findOneByIdentifier('tshirt-the-witcher-3');

        $this->calculateProject($this::$project);

        $this->checkAttributeGroupCompleteness(
            [
                'general' => [
                    'has_at_least_one_required_attribute_filled' => '0',
                    'is_complete' => '1'
                ],
                'marketing' => [
                    'has_at_least_one_required_attribute_filled' => '1',
                    'is_complete' => '0'
                ],
                'technical' => [
                    'has_at_least_one_required_attribute_filled' => '0',
                    'is_complete' => '0'
                ],
            ],
            $product
        );
        $this->checkTheNumberOfAttributeGroupCompleteness();
    }

    /**
     * When you recalculate a project it must have the same number of attribute group completeness
     */
    public function testThatTheProjectRecalculationDoesNotAddAttributeGroupCompleteness()
    {
        $this->reCalculateProject($this::$project);

        $this->checkTheNumberOfAttributeGroupCompleteness();
    }

    /**
     * Check that we get the value of product properties on the right channel, locale and we are able to get the
     * value for every attributes
     *
     * For that test all value should be empties, the "other" attribute group completeness should be "to do"
     *
     * Note: "other" has not a filled property for en_US and ecommerce but it has for mobile and fr_FR
     *
     * Product: empty-technical-product
     * Channel: ecommerce
     * Locale: en_US
     */
    public function testThatProductPropertyIsEmpty()
    {
        $project = $this->createProject([
            'label' => 'test-empty-property',
            'locale' => 'en_US',
            'owner'=> 'admin',
            'channel' => 'ecommerce',
            'product_filters' =>[
                [
                    'field' => 'sku',
                    'operator' => '=',
                    'value' => 'empty-technical-product',
                    'context' => ['locale' => 'en_US', 'scope' => 'ecommerce'],
                ],
            ],
        ]);

        $this->calculateProject($project);

        $product = $this->get('pim_catalog.repository.product')->findOneByIdentifier('empty-technical-product');

        $this->checkAttributeGroupCompleteness(
            [
                'other' => [
                    'has_at_least_one_required_attribute_filled' => '0',
                    'is_complete' => '0'
                ]
            ],
            $product
        );
    }

    /**
     * For that test all value should be empties, the "other" attribute group completeness should be "done"
     *
     * Note: We need to insert reference data (color and fabric) to check that "other" is complete
     *
     * Product : full-technical-product
     * Channel: ecommerce
     * Locale: en_US
     *
     * TODO : Check asset collection.
     */
    public function testThatProductPropertyIsFull()
    {
        $this->getConnection()->insert('acme_reference_data_color', [
            'code' => 'red',
            'name' => 'red',
            'hex' => '#FF0000',
            'red' => 1,
            'green' => 1,
            'blue' => 1,
            'hue' => 1,
            'hslSaturation' => 1,
            'light' => 1,
            'hsvSaturation' => 1,
            'value' => 1,
            'sortOrder' => 10
        ]);

        $this->getConnection()->insert('acme_reference_data_fabric', [
            'code' => 'latex',
            'name' => 'Latex',
            'sortOrder' => 10
        ]);

        $product = $this->get('pim_catalog.repository.product')->findOneByIdentifier('full-technical-product');

        $this->get('pim_catalog.updater.product')->update($product, [
            'values' => [
                'simple_reference_data_attribute' => [[
                    'locale' => null,
                    'scope' => null,
                    'data' => 'red',
                ]],
                'multi_reference_data_attribute' => [[
                    'locale' => null,
                    'scope' => null,
                    'data' => ['latex'],
                ]]
            ]
        ]);
        $this->get('pim_catalog.saver.product')->save($product);

        $project = $this->createProject([
            'label' => 'test-full-property',
            'locale' => 'en_US',
            'owner'=> 'admin',
            'channel' => 'ecommerce',
            'product_filters' =>[
                [
                    'field' => 'sku',
                    'operator' => '=',
                    'value' => 'full-technical-product',
                    'context' => ['locale' => 'en_US', 'scope' => 'ecommerce'],
                ],
            ],
        ]);

        $this->calculateProject($project);

        $this->checkAttributeGroupCompleteness(
            [
                'other' => [
                    'has_at_least_one_required_attribute_filled' => '0',
                    'is_complete' => '1'
                ]
            ],
            $product
        );
    }

    /**
     * Check that the attribute group completeness is well calculated
     *
     * @param array            $expectedAttributeGroupCompleteness
     * @param ProductInterface $product
     */
    private function checkAttributeGroupCompleteness(array $expectedAttributeGroupCompleteness, $product)
    {
        $sql = <<<SQL
SELECT `has_at_least_one_required_attribute_filled`, `is_complete`
FROM `pimee_activity_manager_completeness_per_attribute_group`
WHERE `attribute_group_id` = :attribute_group_id
AND product_id = :product_id;
SQL;

        foreach ($expectedAttributeGroupCompleteness as $group => $expectedCompleteness) {
            $attributeGroupId = $this->get('pim_catalog.repository.attribute_group')
                ->findOneByIdentifier($group)
                ->getId();

            $actualCompleteness = $this->getConnection()
                ->fetchAssoc($sql, [
                    'attribute_group_id' => $attributeGroupId,
                    'product_id' => $product->getId(),
                ]);

            $this->assertSame(
                $actualCompleteness,
                $expectedCompleteness,
                sprintf('Attribute group completeness is not valid for the attribute group %s', $group)
            );
        }
    }

    /**
     * Check that the calculated number of attribute group completeness is correct.
     *
     * A attribute group completeness is only calculated if the one of those attributes
     * are filled and require by the family.
     */
    private function checkTheNumberOfAttributeGroupCompleteness()
    {
        $productId = $this->get('pim_catalog.repository.product')
            ->findOneByIdentifier('tshirt-the-witcher-3')
            ->getId();

        $sql = <<<SQL
SELECT COUNT(*)
FROM `pimee_activity_manager_completeness_per_attribute_group`
WHERE `product_id` = :product_id
AND `channel_id` = :channel_id
AND `locale_id` = :locale_id
SQL;

        $numberOfRow = (int) $this->getConnection()->fetchColumn($sql, [
            'product_id' => $productId,
            'channel_id' => $this::$project->getChannel()->getId(),
            'locale_id' => $this::$project->getLocale()->getId(),
        ]);

        $this->assertSame(
            $numberOfRow,
            3,
            sprintf('Invalid number of calculated attribute group completeness for the product %s', $productId)
        );
    }
}
