<?php

declare(strict_types=1);

namespace Akeneo\Pim\Enrichment\Component\Product\Job;

use Akeneo\Pim\Enrichment\Component\Product\EntityWithFamilyVariant\KeepOnlyValuesForVariation;
use Akeneo\Pim\Enrichment\Component\Product\Model\ProductInterface;
use Akeneo\Pim\Enrichment\Component\Product\Model\ProductModel;
use Akeneo\Pim\Enrichment\Component\Product\Model\ProductModelInterface;
use Akeneo\Pim\Enrichment\Component\Product\Query\Filter\Operators;
use Akeneo\Pim\Enrichment\Component\Product\Query\ProductQueryBuilderFactoryInterface;
use Akeneo\Tool\Component\Batch\Model\StepExecution;
use Akeneo\Tool\Component\Connector\Step\TaskletInterface;
use Akeneo\Tool\Component\StorageUtils\Cache\EntityManagerClearerInterface;
use Akeneo\Tool\Component\StorageUtils\Cursor\CursorInterface;
use Akeneo\Tool\Component\StorageUtils\Repository\IdentifiableObjectRepositoryInterface;
use Akeneo\Tool\Component\StorageUtils\Saver\BulkSaverInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;

/**
 * @author    Adrien Pétremann <adrien.petremann@akeneo.com>
 * @copyright 2017 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 */
class ComputeFamilyVariantStructureChangesTasklet implements TaskletInterface
{
    /** @var StepExecution */
    private $stepExecution;

    /** @var IdentifiableObjectRepositoryInterface */
    private $familyVariantRepository;

    /** @var ProductQueryBuilderFactoryInterface */
    private $productQueryBuilderFactory;

    /** @var BulkSaverInterface */
    private $productSaver;

    /** @var BulkSaverInterface */
    private $productModelSaver;

    /** @var KeepOnlyValuesForVariation */
    private $keepOnlyValuesForVariation;

    /** @var ValidatorInterface */
    private $validator;

    /** @var int */
    private $batchSize;

    /** @var EntityManagerClearerInterface */
    private $cacheClearer;

    /**
     * @param IdentifiableObjectRepositoryInterface $familyVariantRepository
     * @param ProductQueryBuilderFactoryInterface   $productQueryBuilderFactory
     * @param BulkSaverInterface                    $productSaver
     * @param BulkSaverInterface                    $productModelSaver
     * @param KeepOnlyValuesForVariation            $keepOnlyValuesForVariation
     * @param ValidatorInterface                    $validator
     * @param EntityManagerClearerInterface         $cacheClearer
     * @param int                                   $batchSize
     */
    public function __construct(
        IdentifiableObjectRepositoryInterface $familyVariantRepository,
        ProductQueryBuilderFactoryInterface $productQueryBuilderFactory,
        BulkSaverInterface $productSaver,
        BulkSaverInterface $productModelSaver,
        KeepOnlyValuesForVariation $keepOnlyValuesForVariation,
        ValidatorInterface $validator,
        EntityManagerClearerInterface $cacheClearer,
        int $batchSize = 100
    ) {
        $this->familyVariantRepository = $familyVariantRepository;
        $this->productQueryBuilderFactory = $productQueryBuilderFactory;
        $this->productSaver = $productSaver;
        $this->productModelSaver = $productModelSaver;
        $this->keepOnlyValuesForVariation = $keepOnlyValuesForVariation;
        $this->validator = $validator;
        $this->batchSize = $batchSize;
        $this->cacheClearer = $cacheClearer;
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
                $this->cacheClearer->clear();
                $productModels = [];
            }

            if (count($products) >= $this->batchSize) {
                $this->validateProducts($products);
                $this->productSaver->saveAll($products);
                $this->cacheClearer->clear();
                $products = [];
            }
        }

        if (!empty($productModels)) {
            $this->validateProductModels($productModels);
            $this->productModelSaver->saveAll($productModels);
            $this->cacheClearer->clear();
        }

        if (!empty($products)) {
            $this->validateProducts($products);
            $this->productSaver->saveAll($products);
            $this->cacheClearer->clear();
        }
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
                    sprintf(
                        'Validation error for ProductModel with code "%s" during family variant structure change',
                        $productModel->getCode()
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
                    sprintf(
                        'Validation error for Product with identifier "%s" during family variant structure change',
                        $product->getIdentifier()
                    )
                );
            }
        }
    }
}
