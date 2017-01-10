<?php

namespace TestEnterprise\Integration\ActivityManager;


use PimEnterprise\Component\ActivityManager\Model\ProjectInterface;

class PreProcessingOneProductCase extends ActivityManagerTestCase
{
    /** @var ProjectInterface */
    private static $project;

    /**
     * Create a project with only one product to test that the pre processed data are well calculted
     *
     * Product : tshirt-the-witcher-3
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

        $this->calculateProject($this::$project);

        $this->checkAttributeGroupCompleteness();
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
     * Check that the attribute group completeness is well calculated
     */
    private function checkAttributeGroupCompleteness()
    {
        $expectedAttributeGroupCompleteness = [
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
            'other' => [
                'has_at_least_one_required_attribute_filled' => '0',
                'is_complete' => '0'
            ],
        ];

        $sql = <<<SQL
SELECT `has_at_least_one_required_attribute_filled`, `is_complete`
FROM `pimee_activity_manager_completeness_per_attribute_group`
WHERE `attribute_group_id` = :attribute_group_id
SQL;

        foreach ($expectedAttributeGroupCompleteness as $group => $expectedCompleteness) {
            $attributeGroupId = $this->get('pim_catalog.repository.attribute_group')
                ->findOneByIdentifier($group)
                ->getId();

            $actualCompleteness = $this->getConnection()
                ->fetchAssoc($sql, ['attribute_group_id' => $attributeGroupId]);

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
            4,
            sprintf('Invalid number of calculated attribute group completeness for the product %s', $productId)
        );
    }
}
