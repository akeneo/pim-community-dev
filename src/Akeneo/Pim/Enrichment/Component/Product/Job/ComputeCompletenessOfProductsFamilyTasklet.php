<?php

declare(strict_types=1);

namespace Akeneo\Pim\Enrichment\Component\Product\Job;

use Akeneo\Pim\Enrichment\Component\Product\Query\Filter\Operators;
use Akeneo\Pim\Enrichment\Component\Product\Query\ProductQueryBuilderFactoryInterface;
use Akeneo\Pim\Structure\Component\Model\FamilyInterface;
use Akeneo\Tool\Component\Batch\Job\UndefinedJobParameterException;
use Akeneo\Tool\Component\Batch\Model\StepExecution;
use Akeneo\Tool\Component\Connector\Step\TaskletInterface;
use Akeneo\Tool\Component\StorageUtils\Cache\EntityManagerClearerInterface;
use Akeneo\Tool\Component\StorageUtils\Cursor\CursorInterface;
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

    /** @var StepExecution */
    private $stepExecution;

    /** @var IdentifiableObjectRepositoryInterface */
    private $familyRepository;

    /** @var ProductQueryBuilderFactoryInterface */
    private $productQueryBuilderFactory;

    /** @var BulkSaverInterface */
    private $bulkProductSaver;

    /** @var EntityManagerClearerInterface */
    private $cacheClearer;

    /**
     * @param IdentifiableObjectRepositoryInterface $familyRepository
     * @param ProductQueryBuilderFactoryInterface   $productQueryBuilderFactory
     * @param BulkSaverInterface                    $bulkProductSaver
     * @param EntityManagerClearerInterface|null    $cacheClearer
     */
    public function __construct(
        IdentifiableObjectRepositoryInterface $familyRepository,
        ProductQueryBuilderFactoryInterface $productQueryBuilderFactory,
        BulkSaverInterface $bulkProductSaver,
        EntityManagerClearerInterface $cacheClearer = null
    ) {
        $this->familyRepository = $familyRepository;
        $this->productQueryBuilderFactory = $productQueryBuilderFactory;
        $this->bulkProductSaver = $bulkProductSaver;
        $this->cacheClearer = $cacheClearer;
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
     * @return FamilyInterface
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
     *
     * @param FamilyInterface $family
     */
    private function computeCompletenesses(FamilyInterface $family): void
    {
        $productsToSave = $this->findProductsForFamily($family);

        $productBatch = [];
        foreach ($productsToSave as $product) {
            $productBatch[] = $product;

            if (self::BATCH_SIZE === count($productBatch)) {
                $this->bulkProductSaver->saveAll($productBatch);
                $this->cacheClearer->clear();
                $productBatch = [];
            }
        }

        $this->bulkProductSaver->saveAll($productBatch);
        $this->cacheClearer->clear();
    }

    /**
     * Returns a cursor of all products belonging to the family.
     *
     * @param FamilyInterface $family
     *
     * @return CursorInterface
     */
    private function findProductsForFamily(FamilyInterface $family): CursorInterface
    {
        $pqb = $this->productQueryBuilderFactory->create();
        $pqb->addFilter('family', Operators::IN_LIST, [$family->getCode()]);

        return $pqb->execute();
    }
}
