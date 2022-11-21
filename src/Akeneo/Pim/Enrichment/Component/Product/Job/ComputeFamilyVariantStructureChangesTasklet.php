<?php

declare(strict_types=1);

namespace Akeneo\Pim\Enrichment\Component\Product\Job;

use Akeneo\Pim\Enrichment\Component\Product\EntityWithFamilyVariant\KeepOnlyValuesForVariation;
use Akeneo\Pim\Enrichment\Component\Product\Model\ProductInterface;
use Akeneo\Pim\Enrichment\Component\Product\Model\ProductModel;
use Akeneo\Pim\Enrichment\Component\Product\Model\ProductModelInterface;
use Akeneo\Pim\Enrichment\Component\Product\Query\Filter\Operators;
use Akeneo\Pim\Enrichment\Component\Product\Query\ProductQueryBuilderFactoryInterface;
use Akeneo\Tool\Component\Batch\Event\EventInterface;
use Akeneo\Tool\Component\Batch\Event\StepExecutionEvent;
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
    private IdentifiableObjectRepositoryInterface $familyVariantRepository;
    private ProductQueryBuilderFactoryInterface $productQueryBuilderFactory;
    private BulkSaverInterface $productSaver;
    private BulkSaverInterface $productModelSaver;
    private KeepOnlyValuesForVariation $keepOnlyValuesForVariation;
    private ValidatorInterface $validator;
    private EventDispatcherInterface $eventDispatcher;
    private int $batchSize;

    public function __construct(
        IdentifiableObjectRepositoryInterface $familyVariantRepository,
        ProductQueryBuilderFactoryInterface $productQueryBuilderFactory,
        BulkSaverInterface $productSaver,
        BulkSaverInterface $productModelSaver,
        KeepOnlyValuesForVariation $keepOnlyValuesForVariation,
        ValidatorInterface $validator,
        EventDispatcherInterface $eventDispatcher,
        int $batchSize = 100
    ) {
        $this->familyVariantRepository = $familyVariantRepository;
        $this->productQueryBuilderFactory = $productQueryBuilderFactory;
        $this->productSaver = $productSaver;
        $this->productModelSaver = $productModelSaver;
        $this->keepOnlyValuesForVariation = $keepOnlyValuesForVariation;
        $this->validator = $validator;
        $this->batchSize = $batchSize;
        $this->eventDispatcher = $eventDispatcher;
    }

    /**
     * {@inheritdoc}
     */
    public function setStepExecution(StepExecution $stepExecution)
    {
        $this->stepExecution = $stepExecution;
    }

    /**
     * {@inheritdoc}
     */
    public function execute()
    {
        $jobParameters = $this->stepExecution->getJobParameters();
        $familyVariantCodes = $jobParameters->get('family_variant_codes');

        foreach ($familyVariantCodes as $familyVariantCode) {
            $familyVariant = $this->familyVariantRepository->findOneByIdentifier($familyVariantCode);
            $levelNumber = $familyVariant->getNumberOfLevel();

            while ($levelNumber >= ProductModel::ROOT_VARIATION_LEVEL) {
                if (ProductModel::ROOT_VARIATION_LEVEL === $levelNumber) {
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

    private function updateRootProductModels(string $familyVariant)
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

    private function updateVariantProducts(string $familyVariant)
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

    private function updateSubProductModels(string $familyVariant)
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
                $this->validateProductModels($productModels);
                $this->productModelSaver->saveAll($productModels);
                $this->clearBatchCaches();
                $productModels = [];
            }

            if (count($products) >= $this->batchSize) {
                $this->validateProducts($products);
                $this->productSaver->saveAll($products);
                $this->clearBatchCaches();
                $products = [];
            }
        }

        if (!empty($productModels)) {
            $this->validateProductModels($productModels);
            $this->productModelSaver->saveAll($productModels);
            $this->clearBatchCaches();
        }

        if (!empty($products)) {
            $this->validateProducts($products);
            $this->productSaver->saveAll($products);
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
     *
     * @throws \LogicException
     */
    private function validateProductModels(array $productModels): void
    {
        foreach ($productModels as $productModel) {
            $violations = $this->validator->validate($productModel);

            if ($violations->count() !== 0) {
                throw new \LogicException(
                    $this->buildErrorMessage(
                        sprintf(
                            'One or more validation errors occured for ProductModel with code "%s" during family variant structure change:',
                            $productModel->getCode()
                        ),
                        $violations
                    )
                );
            }
        }
    }

    /**
     * @param ProductInterface[] $products
     *
     * @throws \LogicException
     */
    private function validateProducts(array $products): void
    {
        foreach ($products as $product) {
            $violations = $this->validator->validate($product);

            if ($violations->count() !== 0) {
                throw new \LogicException(
                    $this->buildErrorMessage(
                        sprintf(
                            'One or more validation errors occured for Product with identifier "%s" during family variant structure change:',
                            $product->getIdentifier()
                        ),
                        $violations
                    )
                );
            }
        }
    }

    private function buildErrorMessage(
        string $baseMessage,
        ConstraintViolationListInterface $constraintViolationList
    ): string {
        $errorMessage = $baseMessage;
        foreach ($constraintViolationList as $violation) {
            $errorMessage .= sprintf("\n  - %s", $violation->getMessage());
        }

        return $errorMessage;
    }
}
