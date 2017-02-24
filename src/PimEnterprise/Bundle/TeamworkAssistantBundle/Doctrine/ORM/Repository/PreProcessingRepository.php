<?php

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2016 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace PimEnterprise\Bundle\TeamworkAssistantBundle\Doctrine\ORM\Repository;

use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\EntityManagerInterface;
use Pim\Component\Catalog\Model\ProductInterface;
use PimEnterprise\Bundle\TeamworkAssistantBundle\Doctrine\ORM\TableNameMapper;
use PimEnterprise\Component\TeamworkAssistant\Model\ProjectInterface;
use PimEnterprise\Component\TeamworkAssistant\Repository\PreProcessingRepositoryInterface;

/**
 * Fills the pre processing table with the given data
 *
 * @author Olivier Soulet <olivier.soulet@akeneo.com>
 */
class PreProcessingRepository implements PreProcessingRepositoryInterface
{
    /** @var EntityManagerInterface */
    protected $entityManager;

    /** @var TableNameMapper */
    protected $tableNameMapper;

    /**
     * @param EntityManagerInterface $objectManager
     */
    public function __construct(EntityManagerInterface $objectManager, TableNameMapper $tableNameMapper)
    {
        $this->entityManager = $objectManager;
        $this->tableNameMapper = $tableNameMapper;
    }

    /**
     * {@inheritdoc}
     */
    public function isPreProcessable(ProductInterface $product, ProjectInterface $project)
    {
        $connection = $this->entityManager->getConnection();
        $sqlTable = $this->tableNameMapper->getTableName('pimee_teamwork_assistant.completeness_per_attribute_group');

        $query = <<<SQL
SELECT MIN(`attribute_group_completeness`.`calculated_at`)
FROM $sqlTable AS `attribute_group_completeness`
WHERE `attribute_group_completeness`.`product_id` = :product_id
AND `attribute_group_completeness`.`channel_id` = :channel_id
AND `attribute_group_completeness`.`locale_id` = :locale_id
SQL;

        $calculatedAt = $connection->fetchColumn($query, [
            'product_id' => $product->getId(),
            'channel_id' => $project->getChannel()->getId(),
            'locale_id'  => $project->getLocale()->getId(),
        ]);

        if (null === $calculatedAt) {
            return true;
        }

        $calculatedAt = new \DateTime($calculatedAt);

        return $calculatedAt < $product->getUpdated();
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
        $sqlTable = $this->tableNameMapper->getTableName('pimee_teamwork_assistant.completeness_per_attribute_group');

        $connection->delete(
            $sqlTable,
            [
                'product_id' => $product->getId(),
                'channel_id' => $project->getChannel()->getId(),
                'locale_id'  => $project->getLocale()->getId(),
            ]
        );

        foreach ($attributeGroupCompleteness as $attributeGroup) {
            $connection->insert(
                $sqlTable,
                [
                    'product_id'                                 => $product->getId(),
                    'channel_id'                                 => $project->getChannel()->getId(),
                    'locale_id'                                  => $project->getLocale()->getId(),
                    'attribute_group_id'                         => $attributeGroup->getAttributeGroupId(),
                    'has_at_least_one_required_attribute_filled' => $attributeGroup->hasAtLeastOneAttributeFilled(),
                    'is_complete'                                => $attributeGroup->isComplete(),
                    'calculated_at'                              => $attributeGroup->getCalculatedAt()
                        ->format('Y-m-d H:i:s'),
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
        $sqlTable = $this->tableNameMapper->getTableName('pimee_teamwork_assistant.project_product');

        $connection->insert($sqlTable, [
            'project_id' => $project->getId(),
            'product_id' => $product->getId(),
        ]);
    }

    /**
     * {@inheritdoc}
     */
    public function link(ProductInterface $product, Collection $categories)
    {
        $connection = $this->entityManager->getConnection();
        $sqlTable = $this->tableNameMapper->getTableName('pimee_teamwork_assistant.product_category');
        $productId = $product->getId();

        $connection->delete($sqlTable, [
            'product_id' => $productId
        ]);

        foreach ($categories as $category) {
            $connection->insert($sqlTable, [
                'product_id'  => $productId,
                'category_id' => $category->getId(),
            ]);
        }
    }

    /**
     * {@inheritdoc}
     */
    public function prepareProjectCalculation(ProjectInterface $project)
    {
        $connection = $this->entityManager->getConnection();
        $sqlTable = $this->tableNameMapper->getTableName('pimee_teamwork_assistant.project_product');
        $projectId = $project->getId();

        $connection->delete($sqlTable, [
            'project_id' => $projectId,
        ]);
    }

    /**
     * {@inheritdoc}
     */
    public function remove(ProjectInterface $project)
    {
        $connection = $this->entityManager->getConnection();
        $sqlTable = $this->tableNameMapper->getTableName('pimee_teamwork_assistant.project_product');
        $projectId = $project->getId();
        $query = <<<SQL
DELETE completeness
FROM pimee_teamwork_assistant_completeness_per_attribute_group AS completeness
INNER JOIN pimee_teamwork_assistant_project_product AS project_product1
    ON project_product1.product_id = completeness.product_id
LEFT OUTER JOIN pimee_teamwork_assistant_project_product AS project_product2
    ON project_product2.product_id = completeness.product_id AND project_product2.project_id <> :project_id
WHERE project_product1.project_id = :project_id AND project_product2.product_id IS NULL
SQL;

        $connection->executeUpdate($query, ['project_id' => $projectId]);
        $connection->delete($sqlTable, [
            'project_id' => $projectId,
        ]);
    }
}
