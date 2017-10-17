<?php

declare(strict_types=1);

namespace Pim\Component\Catalog\Job;

use Akeneo\Component\Batch\Model\StepExecution;
use Akeneo\Component\StorageUtils\Cursor\CursorInterface;
use Akeneo\Component\StorageUtils\Detacher\BulkObjectDetacherInterface;
use Akeneo\Component\StorageUtils\Repository\IdentifiableObjectRepositoryInterface;
use Akeneo\Component\StorageUtils\Saver\BulkSaverInterface;
use Pim\Component\Catalog\Manager\CompletenessManager;
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
class ComputeCompletenessOfProductsFamily implements TaskletInterface
{
    private const BATCH_SIZE = 100;

    /** @var StepExecution */
    private $stepExecution;

    /** @var IdentifiableObjectRepositoryInterface */
    private $familyRepository;

    /** @var CompletenessManager */
    private $completenessManager;

    /** @var ProductQueryBuilderFactoryInterface */
    private $productQueryBuilderFactory;

    /** @var BulkObjectDetacherInterface */
    private $bulkObjectDetacher;

    /** @var BulkSaverInterface */
    private $bulkProductSaver;

    /**
     * @param IdentifiableObjectRepositoryInterface $familyRepository
     * @param CompletenessManager                   $completenessManager
     * @param ProductQueryBuilderFactoryInterface   $productQueryBuilderFactory
     * @param BulkObjectDetacherInterface           $bulkObjectDetacher
     * @param BulkSaverInterface                    $bulkProductSaver
     */
    public function __construct(
        IdentifiableObjectRepositoryInterface $familyRepository,
        CompletenessManager $completenessManager,
        ProductQueryBuilderFactoryInterface $productQueryBuilderFactory,
        BulkSaverInterface $bulkProductSaver,
        BulkObjectDetacherInterface $bulkObjectDetacher
    ) {
        $this->familyRepository = $familyRepository;
        $this->completenessManager = $completenessManager;
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
     */
    public function execute(): void
    {
        $jobParameters = $this->stepExecution->getJobParameters();
        $familyCode = $jobParameters->get('family_code');

        $this->resetCompletenessOfProductsForFamily($familyCode);
        $this->computeCompletenesses($familyCode);
    }

    /**
     * Resets the completeness of products belonging to the family.
     *
     * @param string $familyCode
     */
    private function resetCompletenessOfProductsForFamily(string $familyCode): void
    {
        $family = $this->familyRepository->findOneByIdentifier($familyCode);
        $this->completenessManager->scheduleForFamily($family);
    }

    /**
     * Recompute the completenesses of all products belonging to the family by calling 'save' on them.
     *
     * @param string $familyCode
     */
    private function computeCompletenesses(string $familyCode): void
    {
        $productToSave = $this->findProductsForFamily($familyCode);

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
     * @param string $familyCode
     *
     * @return CursorInterface
     */
    private function findProductsForFamily(string $familyCode): CursorInterface
    {
        $pqb = $this->productQueryBuilderFactory->create();
        $pqb->addFilter('family', Operators::IN_LIST, [$familyCode]);

        return $pqb->execute();
    }
}
