<?php

declare(strict_types=1);

namespace Akeneo\Pim\Enrichment\Component\Product\Job;

use Akeneo\Pim\Enrichment\Component\Product\EntityWithFamilyVariant\KeepOnlyValuesForVariation;
use Akeneo\Pim\Enrichment\Component\Product\Model\EntityWithFamilyVariantInterface;
use Akeneo\Pim\Enrichment\Component\Product\Model\ProductInterface;
use Akeneo\Pim\Enrichment\Component\Product\Model\ProductModelInterface;
use Akeneo\Pim\Enrichment\Component\Product\Query\Filter\Operators;
use Akeneo\Pim\Enrichment\Component\Product\Query\ProductQueryBuilderFactoryInterface;
use Akeneo\Tool\Component\Batch\Event\EventInterface;
use Akeneo\Tool\Component\Batch\Event\StepExecutionEvent;
use Akeneo\Tool\Component\Batch\Item\DataInvalidItem;
use Akeneo\Tool\Component\Batch\Model\StepExecution;
use Akeneo\Tool\Component\Connector\Step\TaskletInterface;
use Akeneo\Tool\Component\StorageUtils\Cursor\CursorInterface;
use Akeneo\Tool\Component\StorageUtils\Repository\IdentifiableObjectRepositoryInterface;
use Akeneo\Tool\Component\StorageUtils\Saver\BulkSaverInterface;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\Validator\ConstraintViolationListInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;

/**
 * @author    Adrien Pétremann <adrien.petremann@akeneo.com>
 * @copyright 2017 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 */
class ComputeFamilyVariantStructureChangesTasklet implements TaskletInterface
{
    private ?StepExecution $stepExecution = null;

    public function __construct(
        private readonly IdentifiableObjectRepositoryInterface $familyVariantRepository,
        private readonly ProductQueryBuilderFactoryInterface $productQueryBuilderFactory,
        private readonly BulkSaverInterface $productSaver,
        private readonly BulkSaverInterface $productModelSaver,
        private readonly KeepOnlyValuesForVariation $keepOnlyValuesForVariation,
        private readonly ValidatorInterface $validator,
        private readonly EventDispatcherInterface $eventDispatcher,
        private readonly int $batchSize = 100
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
     */
    public function execute(): void
    {
        $jobParameters = $this->stepExecution->getJobParameters();
        $familyVariantCodes = $jobParameters->get('family_variant_codes');

        foreach ($familyVariantCodes as $familyVariantCode) {
            $familyVariant = $this->familyVariantRepository->findOneByIdentifier($familyVariantCode);
            $levelNumber = $familyVariant->getNumberOfLevel();

            while ($levelNumber >= EntityWithFamilyVariantInterface::ROOT_VARIATION_LEVEL) {
                if (EntityWithFamilyVariantInterface::ROOT_VARIATION_LEVEL === $levelNumber) {
                    $this->updateRootProductModels($familyVariantCode);
                } elseif ($levelNumber === $familyVariant->getNumberOfLevel()) {
                    $this->updateVariantProducts($familyVariantCode);
                } else {
                    $this->updateSubProductModels($familyVariantCode);
                }

                $levelNumber--;
            }
        }
    }

    private function updateRootProductModels(string $familyVariant): void
    {
        $pmqb = $this->productQueryBuilderFactory->create([
            'filters' => [
                ['field' => 'entity_type', 'operator' => Operators::EQUALS, 'value' => ProductModelInterface::class],
                ['field' => 'family_variant', 'operator' => Operators::IN_LIST, 'value' => [$familyVariant]],
                ['field' => 'parent', 'operator' => Operators::IS_EMPTY, 'value' => null]
            ]
        ]);

        $this->updateValuesOfEntities($pmqb->execute());
    }

    private function updateVariantProducts(string $familyVariant): void
    {
        $pmqb = $this->productQueryBuilderFactory->create([
            'filters' => [
                ['field' => 'entity_type', 'operator' => Operators::EQUALS, 'value' => ProductInterface::class],
                ['field' => 'family_variant', 'operator' => Operators::IN_LIST, 'value' => [$familyVariant]],
                ['field' => 'parent', 'operator' => Operators::IS_NOT_EMPTY, 'value' => null]
            ]
        ]);

        $this->updateValuesOfEntities($pmqb->execute());
    }

    private function updateSubProductModels(string $familyVariant): void
    {
        $pmqb = $this->productQueryBuilderFactory->create([
            'filters' => [
                ['field' => 'entity_type', 'operator' => Operators::EQUALS, 'value' => ProductModelInterface::class],
                ['field' => 'family_variant', 'operator' => Operators::IN_LIST, 'value' => [$familyVariant]],
                ['field' => 'parent', 'operator' => Operators::IS_NOT_EMPTY, 'value' => null]
            ]
        ]);

        $this->updateValuesOfEntities($pmqb->execute());
    }

    private function updateValuesOfEntities(CursorInterface $entities): void
    {
        $products = [];
        $productModels = [];
        foreach ($entities as $entity) {
            $this->keepOnlyValuesForVariation->updateEntitiesWithFamilyVariant([$entity]);

            if ($entity instanceof ProductModelInterface) {
                $productModels[] = $entity;
            } else {
                $products[] = $entity;
            }

            if (count($productModels) >= $this->batchSize) {
                $validProductModels = $this->validateProductModels($productModels);
                $this->productModelSaver->saveAll($validProductModels);
                $this->stepExecution->incrementSummaryInfo('process', count($validProductModels));
                $this->clearBatchCaches();
                $productModels = [];
            }

            if (count($products) >= $this->batchSize) {
                $validProducts = $this->validateProducts($products);
                $this->stepExecution->incrementSummaryInfo('process', count($validProducts));
                $this->productSaver->saveAll($validProducts);
                $this->clearBatchCaches();
                $products = [];
            }
        }

        if (!empty($productModels)) {
            $validProductModels = $this->validateProductModels($productModels);
            $this->productModelSaver->saveAll($validProductModels);
            $this->stepExecution->incrementSummaryInfo('process', count($validProductModels));
            $this->clearBatchCaches();
        }

        if (!empty($products)) {
            $validProducts = $this->validateProducts($products);
            $this->productSaver->saveAll($validProducts);
            $this->stepExecution->incrementSummaryInfo('process', count($validProducts));
            $this->clearBatchCaches();
        }
    }

    private function clearBatchCaches(): void
    {
        // This event will trigger cache clearing wherever it's needed
        $this->eventDispatcher->dispatch(
            new StepExecutionEvent($this->stepExecution),
            EventInterface::ITEM_STEP_AFTER_BATCH
        );
    }

    /**
     * @param ProductModelInterface[] $productModels
     * @return ProductModelInterface[]
     */
    private function validateProductModels(array $productModels): array
    {
        $validProductModels = [];
        foreach ($productModels as $key => $productModel) {
            $violations = $this->validator->validate($productModel);

            if ($violations->count() !== 0) {
                $this->stepExecution->addWarning(
                    $this->buildErrorMessage($violations),
                    [],
                    new DataInvalidItem($productModel)
                );
                $this->stepExecution->incrementSummaryInfo('skip');
            } else {
                $validProductModels[] = $productModel;
            }
        }

        return $validProductModels;
    }

    /**
     * @param ProductInterface[] $products
     * @return ProductInterface[]
     */
    private function validateProducts(array $products): array
    {
        $validProducts = [];
        foreach ($products as $key => $product) {
            $violations = $this->validator->validate($product);

            if ($violations->count() !== 0) {
                $this->stepExecution->addWarning(
                    $this->buildErrorMessage($violations),
                    [],
                    new DataInvalidItem($product)
                );
                $this->stepExecution->incrementSummaryInfo('skip');
            } else {
                $validProducts[] = $product;
            }
        }

        return $validProducts;
    }

    private function buildErrorMessage(
        ConstraintViolationListInterface $constraintViolationList
    ): string {
        $errorMessage = '';
        foreach ($constraintViolationList as $violation) {
            $errorMessage .= sprintf("\n  - %s", $violation->getMessage());
        }

        return $errorMessage;
    }
}
