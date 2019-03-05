<?php

declare(strict_types=1);

namespace Akeneo\Asset\Bundle\Job;

use Akeneo\Asset\Bundle\AttributeType\AttributeTypes;
use Akeneo\Asset\Component\Persistence\Query\Sql\FindFamilyCodesWhereAttributesAreRequiredInterface;
use Akeneo\Pim\Enrichment\Component\Product\Query\Filter\Operators;
use Akeneo\Pim\Enrichment\Component\Product\Query\ProductQueryBuilderFactoryInterface;
use Akeneo\Pim\Structure\Component\Repository\AttributeRepositoryInterface;
use Akeneo\Tool\Component\Batch\Model\StepExecution;
use Akeneo\Tool\Component\Connector\Step\TaskletInterface;
use Akeneo\Tool\Component\StorageUtils\Cursor\CursorInterface;
use Akeneo\Tool\Component\StorageUtils\Detacher\BulkObjectDetacherInterface;
use Akeneo\Tool\Component\StorageUtils\Indexer\BulkIndexerInterface;
use Doctrine\Common\Persistence\ObjectManager;
use Doctrine\DBAL\Connection;
use Doctrine\ORM\EntityManagerInterface;

/**
 * This tasklet is meant to be launched by a job after some assets or asset references are updated.
 * It resets the completeness of products that will be recalculated later by the "pim:completeness:calculate" task.
 * Products need to be in a family where the asset collection attribute is required and to have the asset in the asset
 * collection attribute.
 * Ideally the completeness could be calculated directly instead of being reset, like for other product values.
 *
 * @author Yohan Blain <yohan.blain@akeneo.com>
 */
final class ComputeCompletenessOfProductsLinkedToAssetsTasklet implements TaskletInterface
{
    const BULK_SIZE = 100;

    /** @var AttributeRepositoryInterface */
    private $attributeRepository;

    /** @var ProductQueryBuilderFactoryInterface */
    private $productQueryBuilderFactory;

    /** @var ObjectManager */
    private $entityManager;

    /** @var BulkIndexerInterface */
    private $indexer;

    /** @var BulkObjectDetacherInterface */
    private $bulkDetacher;

    /** @var string */
    private $completenessTableName;

    /** @var StepExecution */
    private $stepExecution;

    /** @var FindFamilyCodesWhereAttributesAreRequiredInterface */
    private $familyCodesQuery;

    /**
     * @param AttributeRepositoryInterface                       $attributeRepository
     * @param ProductQueryBuilderFactoryInterface                $productQueryBuilderFactory
     * @param EntityManagerInterface                             $entityManager
     * @param BulkIndexerInterface                               $indexer
     * @param BulkObjectDetacherInterface                        $bulkDetacher
     * @param string                                             $completenessTableName
     * @param FindFamilyCodesWhereAttributesAreRequiredInterface $familiesCodesQuery
     */
    public function __construct(
        AttributeRepositoryInterface $attributeRepository,
        ProductQueryBuilderFactoryInterface $productQueryBuilderFactory,
        EntityManagerInterface $entityManager,
        BulkIndexerInterface $indexer,
        BulkObjectDetacherInterface $bulkDetacher,
        string $completenessTableName,
        FindFamilyCodesWhereAttributesAreRequiredInterface $familiesCodesQuery
    ) {
        $this->attributeRepository = $attributeRepository;
        $this->productQueryBuilderFactory = $productQueryBuilderFactory;
        $this->entityManager = $entityManager;
        $this->indexer = $indexer;
        $this->bulkDetacher = $bulkDetacher;
        $this->completenessTableName = $completenessTableName;
        $this->familyCodesQuery = $familiesCodesQuery;
    }

    /**
     * {@inheritdoc}
     */
    public function setStepExecution(StepExecution $stepExecution): void
    {
        $this->stepExecution = $stepExecution;
    }

    /**
     * {@inheritdoc}
     */
    public function execute(): void
    {
        $assetCodes = $this->stepExecution->getJobParameters()->get('asset_codes');

        $attributeCodes = $this->attributeRepository->getAttributeCodesByType(AttributeTypes::ASSETS_COLLECTION);

        $familyCodes = $this->familyCodesQuery->find($attributeCodes);

        if (!empty($familyCodes)) {
            foreach ($attributeCodes as $attributeCode) {
                $products = $this->findProductsLinkedToAssetsForAttribute($attributeCode, $assetCodes, $familyCodes);
                $this->resetCompletenessFor($products);
            }
        }
    }

    /**
     * @param string   $attributeCode
     * @param string[] $assetCodes
     * @param string[] $familyCodes
     *
     * @return CursorInterface
     */
    private function findProductsLinkedToAssetsForAttribute(
        string $attributeCode,
        array $assetCodes,
        array $familyCodes
    ): CursorInterface {
        $pqb = $this->productQueryBuilderFactory->create();
        $pqb->addFilter($attributeCode, Operators::IN_LIST, $assetCodes);
        $pqb->addFilter('family', Operators::IN_LIST, $familyCodes);

        return $pqb->execute();
    }

    /**
     * @param CursorInterface $products
     *
     * @throws \Doctrine\DBAL\DBALException
     */
    private function resetCompletenessFor(CursorInterface $products): void
    {
        $bulkedProducts = [];
        $productIds = [];
        $bulkCounter = 0;

        $query = sprintf('DELETE c FROM %s c WHERE c.product_id IN (:productIds)', $this->completenessTableName);

        foreach ($products as $product) {
            $bulkedProducts[] = $product;
            $productIds[] = $product->getId();

            $product->getCompletenesses()->clear();

            if (self::BULK_SIZE === $bulkCounter) {
                $this->entityManager->getConnection()->executeQuery(
                    $query,
                    ['productIds' => $productIds],
                    ['productIds' => Connection::PARAM_INT_ARRAY]
                );
                $this->indexer->indexAll($bulkedProducts);
                $this->bulkDetacher->detachAll($bulkedProducts);

                $bulkedProducts = [];
                $productIds = [];
                $bulkCounter = 0;
            } else {
                $bulkCounter++;
            }
        }

        if (!empty($productIds)) {
            $this->entityManager->getConnection()->executeQuery(
                $query,
                ['productIds' => $productIds],
                ['productIds' => Connection::PARAM_INT_ARRAY]
            );
            $this->indexer->indexAll($bulkedProducts);
            $this->bulkDetacher->detachAll($bulkedProducts);
        }
    }
}
