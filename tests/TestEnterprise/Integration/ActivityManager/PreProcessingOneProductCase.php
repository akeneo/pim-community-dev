<?php

namespace TestEnterprise\Integration\ActivityManager;


use PimEnterprise\Component\ActivityManager\Model\ProjectInterface;

class PreProcessingOneProductCase extends ActivityManagerTestCase
{
    /** @var ProjectInterface */
    private static $project;

    public function testProjectCreationWithOneProduct()
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
    }

    public function testAttributeGroupCompleteness()
    {
        // TODO
        $expectedAttributeGroupCompleteness = [
            'marketing' => [
                'has_at_least_one_required_attribute_filled' => 1,
                'is_complete' => 0
            ],
            'technical' => [
                'has_at_least_one_required_attribute_filled' => 0,
                'is_complete' => 0
            ],
            'other' => [
                'has_at_least_one_required_attribute_filled' => 0,
                'is_complete' => 0
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

            $this->assertSame($expectedCompleteness, $actualCompleteness);
        }
    }

    public function testTheNumberOfLinePreProcessed()
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

        $numberOfRow = $this->getConnection()->fetchColumn($sql, [
            'product_id' => $productId,
            'channel_id' => $this::$project->getChannel()->getId(),
            'locale_id' => $this::$project->getLocale()->getId(),
        ]);

        $this->assertSame($numberOfRow, 3);
    }
}
