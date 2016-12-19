<?php

namespace PimEnterprise\Bundle\ActivityManagerBundle\Doctrine\ORM\Repository;

use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\EntityRepository;
use PimEnterprise\Component\ActivityManager\Repository\PreProcessingRepositoryInterface;

/**
 * Fills the pre processing table with the given data
 *
 * @author Olivier Soulet <olivier.soulet@akeneo.com>
 */
class PreProcessingRepository extends EntityRepository implements PreProcessingRepositoryInterface
{
    /** @var EntityManagerInterface */
    protected $objectManager;

    /** @var string */
    protected $tableName;

    /**
     * @param EntityManagerInterface $objectManager
     * @param string                 $tableName
     */
    public function __construct(EntityManagerInterface $objectManager, $tableName)
    {
        $this->objectManager = $objectManager;
        $this->tableName = $tableName;
    }

    /**
     * {@inheritdoc}
     */
    public function addPreProcessingData($productId, $attributeGroupId, $atLeast, $complete, $channelId, $localeId)
    {
        $this->objectManager->getConnection()->insert($this->tableName, [
            'product_id' => $productId,
            'attribute_group_id' => $attributeGroupId,
            'at_least' => $atLeast,
            'complete' => $complete,
        ]);
    }
}
