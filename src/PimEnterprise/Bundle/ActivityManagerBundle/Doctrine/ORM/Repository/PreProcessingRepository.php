<?php

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2016 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace PimEnterprise\Bundle\ActivityManagerBundle\Doctrine\ORM\Repository;

use Doctrine\ORM\EntityManagerInterface;
use Pim\Component\Catalog\Model\ProductInterface;
use PimEnterprise\Component\ActivityManager\Model\ProjectInterface;
use PimEnterprise\Component\ActivityManager\Repository\PreProcessingRepositoryInterface;

/**
 * Fills the pre processing table with the given data
 *
 * @author Olivier Soulet <olivier.soulet@akeneo.com>
 */
class PreProcessingRepository implements PreProcessingRepositoryInterface
{
    /** @var EntityManagerInterface */
    protected $entityManager;

    /**
     * @param EntityManagerInterface $objectManager
     */
    public function __construct(EntityManagerInterface $objectManager)
    {
        $this->entityManager = $objectManager;
    }

    /**
     * {@inheritdoc}
     */
    public function addAttributeGroupCompleteness(
        ProductInterface $product,
        ProjectInterface $project,
        array $attributeGroupCompleteness
    ) {
        $connection = $this->entityManager->getConnection();

        foreach ($attributeGroupCompleteness as $attributeGroup) {
            $sql = <<<SQL
REPLACE INTO pimee_activity_manager_completeness_per_attribute_group
VALUE (
    :locale_id,
    :channel_id,
    :product_id,
    :attribute_group_id,
    :has_at_least_one_required_attribute_filled,
    :is_complete
)
SQL;

            $connection->executeQuery(
                $sql,
                [
                    'product_id'                                 => $product->getId(),
                    'channel_id'                                 => $project->getChannel()->getId(),
                    'locale_id'                                  => $project->getLocale()->getId(),
                    'attribute_group_id'                         => $attributeGroup->getAttributeGroupId(),
                    'has_at_least_one_required_attribute_filled' => $attributeGroup->hasAtLeastOneAttributeFilled(),
                    'is_complete'                                => $attributeGroup->isComplete(),
                ]
            );
        }
    }

    /**
     * {@inheritdoc}
     */
    public function addProduct(ProjectInterface $project, ProductInterface $product)
    {
        $connection = $this->entityManager->getConnection();

        $connection->insert('pimee_activity_manager_project_product', [
            'project_id' => $project->getId(),
            'product_id' => $product->getId(),
        ]);
    }

    /**
     * {@inheritdoc}
     */
    public function reset(ProjectInterface $project)
    {
        $connection = $this->entityManager->getConnection();
        $projectId = $project->getId();

        $sql = <<<SQL
DELETE `cag`
FROM `pimee_activity_manager_completeness_per_attribute_group` AS `cag`
LEFT JOIN `pimee_activity_manager_project_product` AS `pp` 
	ON `pp`.`product_id` = `cag`.`product_id`
WHERE `pp`.`project_id` = :project_id
SQL;

        $connection->executeUpdate($sql, ['project_id' => $projectId]);
        $connection->delete('pimee_activity_manager_project_product', [
            'project_id' => $projectId,
        ]);
    }

    /**
     * {@inheritdoc}
     */
    public function remove(ProjectInterface $project)
    {
        $connection = $this->entityManager->getConnection();
        $projectId = $project->getId();
        $query = <<<SQL
DELETE completeness
FROM pimee_activity_manager_completeness_per_attribute_group AS completeness
INNER JOIN pimee_activity_manager_project_product AS project_product1
    ON project_product1.product_id = completeness.product_id
LEFT OUTER JOIN pimee_activity_manager_project_product AS project_product2
    ON project_product2.product_id = completeness.product_id AND project_product2.project_id <> :project_id
WHERE project_product1.project_id = :project_id AND project_product2.product_id IS NULL
SQL;

        $connection->executeUpdate($query, ['project_id' => $projectId]);
        $connection->delete('pimee_activity_manager_project_product', [
            'project_id' => $projectId,
        ]);
    }
}
