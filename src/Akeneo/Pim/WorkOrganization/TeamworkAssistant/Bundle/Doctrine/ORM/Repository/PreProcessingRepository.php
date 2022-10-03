<?php

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2016 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Akeneo\Pim\WorkOrganization\TeamworkAssistant\Bundle\Doctrine\ORM\Repository;

use Akeneo\Channel\Infrastructure\Component\Model\ChannelInterface;
use Akeneo\Channel\Infrastructure\Component\Model\LocaleInterface;
use Akeneo\Pim\Enrichment\Component\Product\Model\ProductInterface;
use Akeneo\Pim\WorkOrganization\TeamworkAssistant\Component\Model\ProjectInterface;
use Akeneo\Pim\WorkOrganization\TeamworkAssistant\Component\Repository\PreProcessingRepositoryInterface;
use Doctrine\ORM\EntityManagerInterface;

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
    public function isProcessableAttributeGroupCompleteness(ProductInterface $product, ProjectInterface $project)
    {
        $connection = $this->entityManager->getConnection();

        $query = <<<SQL
SELECT MIN(`attribute_group_completeness`.`calculated_at`)
FROM `pimee_teamwork_assistant_completeness_per_attribute_group` AS `attribute_group_completeness`
WHERE `attribute_group_completeness`.`product_uuid` = :product_uuid
AND `attribute_group_completeness`.`channel_id` = :channel_id
AND `attribute_group_completeness`.`locale_id` = :locale_id
SQL;

        $calculatedAt = $connection->fetchOne($query, [
            'product_uuid' => $product->getUuid()->getBytes(),
            'channel_id' => $project->getChannel()->getId(),
            'locale_id'  => $project->getLocale()->getId(),
        ]);

        if (null === $calculatedAt) {
            return true;
        }

        $calculatedAt = new \DateTime($calculatedAt);

        if (null !== $product->getFamily()) {
            return $calculatedAt < $product->getUpdated() || $calculatedAt < $product->getFamily()->getUpdated();
        }

        return $calculatedAt < $product->getUpdated();
    }

    /**
     * {@inheritdoc}
     */
    public function belongsToAProject(ProductInterface $product)
    {
        $query = <<<SQL
SELECT count(project_id)
FROM pimee_teamwork_assistant_project_product AS project_product
WHERE product_uuid = :product_uuid
SQL;

        $connection = $this->entityManager->getConnection();
        $projects = $connection->fetchOne($query, ['product_uuid' => $product->getUuid()->getBytes()]);

        return 0 < $projects;
    }

    /**
     * {@inheritdoc}
     */
    public function addAttributeGroupCompleteness(
        ProductInterface $product,
        ChannelInterface $channel,
        LocaleInterface $locale,
        array $attributeGroupCompleteness
    ) {
        $connection = $this->entityManager->getConnection();

        $productUuid = $product->getUuid()->getBytes();
        $channelId = $channel->getId();
        $localeId = $locale->getId();

        $connection->delete(
            'pimee_teamwork_assistant_completeness_per_attribute_group',
            [
                'product_uuid' => $productUuid,
                'channel_id' => $channelId,
                'locale_id'  => $localeId,
            ]
        );

        foreach ($attributeGroupCompleteness as $attributeGroup) {
            $connection->insert(
                'pimee_teamwork_assistant_completeness_per_attribute_group',
                [
                    'product_uuid'                               => $productUuid,
                    'channel_id'                                 => $channelId,
                    'locale_id'                                  => $localeId,
                    'attribute_group_id'                         => $attributeGroup->getAttributeGroupId(),
                    'has_at_least_one_required_attribute_filled' => $attributeGroup->hasAtLeastOneAttributeFilled(),
                    'is_complete'                                => $attributeGroup->isComplete(),
                    'calculated_at'                              => $attributeGroup->getCalculatedAt()->format('Y-m-d H:i:s'),
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

        $connection->insert('pimee_teamwork_assistant_project_product', [
            'project_id' => $project->getId(),
            'product_uuid' => $product->getUuid()->getBytes(),
        ]);
    }

    /**
     * {@inheritdoc}
     */
    public function prepareProjectCalculation(ProjectInterface $project)
    {
        $connection = $this->entityManager->getConnection();
        $projectId = $project->getId();

        $connection->delete('pimee_teamwork_assistant_project_product', [
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
FROM pimee_teamwork_assistant_completeness_per_attribute_group AS completeness
INNER JOIN pimee_teamwork_assistant_project_product AS project_product1
    ON project_product1.product_uuid = completeness.product_uuid
LEFT OUTER JOIN pimee_teamwork_assistant_project_product AS project_product2
    ON project_product2.product_uuid = completeness.product_uuid AND project_product2.project_id <> :project_id
WHERE project_product1.project_id = :project_id AND project_product2.product_uuid IS NULL
SQL;

        $connection->executeUpdate($query, ['project_id' => $projectId]);
        $connection->delete('pimee_teamwork_assistant_project_product', [
            'project_id' => $projectId,
        ]);
    }
}
