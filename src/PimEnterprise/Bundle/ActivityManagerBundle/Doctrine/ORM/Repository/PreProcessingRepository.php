<?php

namespace PimEnterprise\Bundle\ActivityManagerBundle\Doctrine\ORM\Repository;

use Doctrine\ORM\EntityManagerInterface;
use PimEnterprise\Component\ActivityManager\Repository\PreProcessingRepositoryInterface;

/**
 * Fills the pre processing table with the given data
 *
 * @author Olivier Soulet <olivier.soulet@akeneo.com>
 */
class PreProcessingRepository implements PreProcessingRepositoryInterface
{
    /** @var EntityManagerInterface */
    protected $objectManager;

    /**
     * @param EntityManagerInterface $objectManager
     */
    public function __construct(EntityManagerInterface $objectManager)
    {
        $this->objectManager = $objectManager;
    }

    /**
     * TODO: manage transaction/error during the project calculation
     *
     * {@inheritdoc}
     */
    public function save($productId, $channelId, $localeId, array $attributeGroupCompleteness)
    {
        $connection = $this->objectManager->getConnection();

        $connection->delete(
            'akeneo_activity_manager_completeness_per_attribute_group',
            [
                'product_id' => $productId,
            ]
        );

        foreach ($attributeGroupCompleteness as $attributeGroup) {
            $connection->insert('akeneo_activity_manager_completeness_per_attribute_group', [
                'product_id' => $productId,
                'channel_id' => $channelId,
                'locale_id' => $localeId,
                'attribute_group_id' => $attributeGroup[0],
                'has_at_least_one_required_attribute_filled' => $attributeGroup[1],
                'is_complete' => $attributeGroup[2],
            ]);
        }
    }
}
