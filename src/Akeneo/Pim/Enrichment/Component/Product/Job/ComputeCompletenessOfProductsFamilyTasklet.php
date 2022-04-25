<?php

declare(strict_types=1);

namespace Akeneo\Pim\Enrichment\Component\Product\Job;

use Akeneo\Pim\Enrichment\Bundle\Elasticsearch\IdentifierResult;
use Akeneo\Pim\Enrichment\Component\Product\Query\Filter\Operators;
use Akeneo\Pim\Enrichment\Component\Product\Query\ProductQueryBuilderFactoryInterface;
use Akeneo\Pim\Structure\Component\Model\FamilyInterface;
use Akeneo\Tool\Component\Batch\Job\UndefinedJobParameterException;
use Akeneo\Tool\Component\Batch\Model\StepExecution;
use Akeneo\Tool\Component\Connector\Step\TaskletInterface;
use Akeneo\Tool\Component\StorageUtils\Cache\EntityManagerClearerInterface;
use Akeneo\Tool\Component\StorageUtils\Cursor\CursorInterface;
use Akeneo\Tool\Component\StorageUtils\Repository\CursorableRepositoryInterface;
use Akeneo\Tool\Component\StorageUtils\Repository\IdentifiableObjectRepositoryInterface;
use Akeneo\Tool\Component\StorageUtils\Saver\BulkSaverInterface;

/**
 * Triggers the computation of the completeness for all products belonging to a family that has been updated by calling
 * save on them.
 *
 * @author    Samir Boulil <samir.boulil@akeneo.com>
 * @copyright 2017 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 */
class ComputeCompletenessOfProductsFamilyTasklet implements TaskletInterface
{
    private const BATCH_SIZE = 100;

    public function __construct(
        private IdentifiableObjectRepositoryInterface $familyRepository,
        private ProductQueryBuilderFactoryInterface $productQueryBuilderFactory,
        private CursorableRepositoryInterface $productRepository,
        private BulkSaverInterface $bulkProductSaver,
        private EntityManagerClearerInterface $cacheClearer
    ) {
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
     *
     * @throws UndefinedJobParameterException
     */
    public function execute(): void
    {
        $family = $this->getFamilyFromJobParameters();
        $this->computeCompletenesses($family);
    }

    /**
     * Get the family instance from the job parameters or null.
     *
     * @throws UndefinedJobParameterException
     * @throws \InvalidArgumentException
     */
    private function getFamilyFromJobParameters(): ?FamilyInterface
    {
        $familyCode = $this->stepExecution->getJobParameters()->get('family_code');
        $family = $this->familyRepository->findOneByIdentifier($familyCode);

        if (null === $family) {
            throw new \InvalidArgumentException(sprintf('Family not found, "%s" given', $familyCode));
        }

        return $family;
    }

    /**
     * Recompute the completenesses of all products belonging to the family by calling 'save' on them.
     */
    private function computeCompletenesses(FamilyInterface $family): void
    {
        $productIdentifiers = $this->findProductIdentifiersForFamily($family);

        $productIdentifierBatch = [];
        /** @var IdentifierResult $productIdentifier */
        foreach ($productIdentifiers as $productIdentifier) {
            $productIdentifierBatch[] = $productIdentifier->getIdentifier();
            if (self::BATCH_SIZE === \count($productIdentifierBatch)) {
                $products = $this->productRepository->getItemsFromIdentifiers($productIdentifierBatch);
                $this->bulkProductSaver->saveAll($products, ['force_save' => true]);
                $this->cacheClearer->clear();
                $productIdentifierBatch = [];
            }
        }

        if (0 < \count($productIdentifierBatch)) {
            $products = $this->productRepository->getItemsFromIdentifiers($productIdentifierBatch);
            $this->bulkProductSaver->saveAll($products, ['force_save' => true]);
            $this->cacheClearer->clear();
        }
    }

    /**
     * Returns a cursor of all product identifiers belonging to the family.
     */
    private function findProductIdentifiersForFamily(FamilyInterface $family): CursorInterface
    {
        $pqb = $this->productQueryBuilderFactory->create();
        $pqb->addFilter('family', Operators::IN_LIST, [$family->getCode()]);

        return $pqb->execute();
    }
}
