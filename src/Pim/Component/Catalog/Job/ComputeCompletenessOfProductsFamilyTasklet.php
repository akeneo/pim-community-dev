<?php

declare(strict_types=1);

namespace Pim\Component\Catalog\Job;

use Akeneo\Component\Batch\Job\UndefinedJobParameterException;
use Akeneo\Component\Batch\Model\StepExecution;
use Akeneo\Component\StorageUtils\Cursor\CursorInterface;
use Akeneo\Component\StorageUtils\Detacher\BulkObjectDetacherInterface;
use Akeneo\Component\StorageUtils\Repository\IdentifiableObjectRepositoryInterface;
use Akeneo\Component\StorageUtils\Saver\BulkSaverInterface;
use Pim\Component\Catalog\Model\FamilyInterface;
use Pim\Component\Catalog\Query\Filter\Operators;
use Pim\Component\Catalog\Query\ProductQueryBuilderFactoryInterface;
use Pim\Component\Connector\Step\TaskletInterface;

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

    /** @var BulkObjectDetacherInterface */
    private $bulkObjectDetacher;

    /** @var BulkSaverInterface */
    private $bulkProductSaver;

    /**
     * @param IdentifiableObjectRepositoryInterface $familyRepository
     * @param ProductQueryBuilderFactoryInterface   $productQueryBuilderFactory
     * @param BulkObjectDetacherInterface           $bulkObjectDetacher
     * @param BulkSaverInterface                    $bulkProductSaver
     */
    public function __construct(
        IdentifiableObjectRepositoryInterface $familyRepository,
        ProductQueryBuilderFactoryInterface $productQueryBuilderFactory,
        BulkSaverInterface $bulkProductSaver,
        BulkObjectDetacherInterface $bulkObjectDetacher
    ) {
        $this->familyRepository = $familyRepository;
        $this->productQueryBuilderFactory = $productQueryBuilderFactory;
        $this->bulkProductSaver = $bulkProductSaver;
        $this->bulkObjectDetacher = $bulkObjectDetacher;
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
        $productToSave = $this->findProductsForFamily($family);

        $productBatch = [];
        foreach ($productToSave as $product) {
            $productBatch[] = $product;

            if (self::BATCH_SIZE === $productBatch) {
                $this->bulkProductSaver->saveAll($productBatch);
                $this->bulkObjectDetacher->detachAll($productBatch);

                $productBatch = [];
            }
        }

        $this->bulkProductSaver->saveAll($productBatch);
        $this->bulkObjectDetacher->detachAll($productBatch);
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
