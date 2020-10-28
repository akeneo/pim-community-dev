<?php

declare(strict_types=1);

namespace Akeneo\Pim\Enrichment\Component\Product\Connector\Job;

use Akeneo\Pim\Enrichment\Component\Product\EntityWithFamilyVariant\KeepOnlyValuesForVariation;
use Akeneo\Pim\Enrichment\Component\Product\Model\EntityWithFamilyVariantInterface;
use Akeneo\Pim\Enrichment\Component\Product\Query\Filter\Operators;
use Akeneo\Pim\Enrichment\Component\Product\Query\ProductQueryBuilderFactoryInterface;
use Akeneo\Pim\Structure\Component\Model\FamilyInterface;
use Akeneo\Tool\Component\Batch\Item\InitializableInterface;
use Akeneo\Tool\Component\Batch\Item\InvalidItemException;
use Akeneo\Tool\Component\Batch\Item\ItemReaderInterface;
use Akeneo\Tool\Component\Batch\Item\TrackableTaskletInterface;
use Akeneo\Tool\Component\Batch\Job\JobRepositoryInterface;
use Akeneo\Tool\Component\Batch\Model\StepExecution;
use Akeneo\Tool\Component\Connector\Step\TaskletInterface;
use Akeneo\Tool\Component\StorageUtils\Cache\EntityManagerClearerInterface;
use Akeneo\Tool\Component\StorageUtils\Cursor\CursorInterface;
use Akeneo\Tool\Component\StorageUtils\Repository\IdentifiableObjectRepositoryInterface;
use Akeneo\Tool\Component\StorageUtils\Saver\BulkSaverInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;

/**
 * For each line of the file of families to import we will:
 * - fetch the corresponding family object,
 * - fetch all the products of this family,
 * - batch save these products.
 *
 * This way, on family, import the family's product completeness will be computed
 * and all family's attributes will be indexed.
 *
 * @author    Olivier Soulet <olivier.soulet@akeneo.com>
 * @copyright 2018 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class ComputeDataRelatedToFamilyProductsTasklet implements TaskletInterface, InitializableInterface, TrackableTaskletInterface
{
    /** @var StepExecution */
    private $stepExecution;

    /** @var IdentifiableObjectRepositoryInterface */
    private $familyRepository;

    /** @var ProductQueryBuilderFactoryInterface */
    private $productQueryBuilderFactory;

    /** @var ItemReaderInterface */
    private $familyReader;

    /** @var KeepOnlyValuesForVariation */
    private $keepOnlyValuesForVariation;

    /** @var ValidatorInterface */
    private $validator;

    /** @var BulkSaverInterface */
    private $productSaver;

    /** @var JobRepositoryInterface */
    private $jobRepository;

    /** @var EntityManagerClearerInterface */
    private $cacheClearer;

    /** @var int */
    private $batchSize;

    public function __construct(
        IdentifiableObjectRepositoryInterface $familyRepository,
        ProductQueryBuilderFactoryInterface $productQueryBuilderFactory,
        ItemReaderInterface $familyReader,
        BulkSaverInterface $productSaver,
        EntityManagerClearerInterface $cacheClearer,
        JobRepositoryInterface $jobRepository,
        KeepOnlyValuesForVariation $keepOnlyValuesForVariation,
        ValidatorInterface $validator,
        int $batchSize
    ) {
        $this->familyRepository = $familyRepository;
        $this->productQueryBuilderFactory = $productQueryBuilderFactory;
        $this->familyReader = $familyReader;
        $this->productSaver = $productSaver;
        $this->jobRepository = $jobRepository;
        $this->cacheClearer = $cacheClearer;
        $this->keepOnlyValuesForVariation = $keepOnlyValuesForVariation;
        $this->validator = $validator;
        $this->batchSize = $batchSize;
    }

    /**
     * {@inheritdoc}
     */
    public function setStepExecution(StepExecution $stepExecution)
    {
        $this->stepExecution = $stepExecution;
    }

    public function count(): int
    {
        return $this->computeTotalItemsToProcess();
    }

    /**
     * {@inheritdoc}
     */
    public function execute()
    {
        $this->initialize();

        while (true) {
            try {
                $familyItem = $this->familyReader->read();
                if (null === $familyItem) {
                    break;
                }
            } catch (InvalidItemException $e) {
                continue;
            }

            $family = $this->familyRepository->findOneByIdentifier($familyItem['code']);
            if (null === $family) {
                $this->stepExecution->incrementSummaryInfo('skip');
                continue;
            }

            $skippedProducts = [];
            $productsToSave = [];
            $products = $this->getProductsForFamily($family);

            foreach ($products as $product) {
                if ($product->isVariant()) {
                    $this->keepOnlyValuesForVariation->updateEntitiesWithFamilyVariant([$product]);

                    if (!$this->isValid($product)) {
                        $this->stepExecution->incrementSummaryInfo('skip');
                        $skippedProducts[] = $product;
                    } else {
                        $productsToSave[] = $product;
                    }
                } else {
                    $productsToSave[] = $product;
                }

                if (0 === (count($productsToSave) + count($skippedProducts)) % $this->batchSize) {
                    $this->saveProducts($productsToSave);
                    $productsToSave = [];
                    $skippedProducts = [];
                    $this->cacheClearer->clear();
                }
            }

            $this->saveProducts($productsToSave);

            $this->cacheClearer->clear();
        }
    }

    /**
     * {@inheritdoc}
     */
    public function initialize()
    {
        $this->cacheClearer->clear();
    }

    /**
     * @param EntityWithFamilyVariantInterface $entityWithFamilyVariant
     *
     * @return bool
     */
    private function isValid(EntityWithFamilyVariantInterface $entityWithFamilyVariant): bool
    {
        $violations = $this->validator->validate($entityWithFamilyVariant);

        return $violations->count() === 0;
    }

    /**
     * @param array $products
     */
    private function saveProducts(array $products): void
    {
        if (empty($products)) {
            return;
        }

        $this->productSaver->saveAll($products);
        $this->stepExecution->incrementSummaryInfo('process', count($products));
        $this->stepExecution->incrementProcessedItems(count($products));
        $this->jobRepository->updateStepExecution($this->stepExecution);
    }

    /**
     * @param FamilyInterface $family
     *
     * @return CursorInterface
     */
    private function getProductsForFamily(FamilyInterface $family): CursorInterface
    {
        $pqb = $this->productQueryBuilderFactory->create();
        $pqb->addFilter('family', Operators::IN_LIST, [$family->getCode()]);

        return $pqb->execute();
    }

    /**
     * Quentin raised some concerns regarding the ability to rewind the familyReader (which can be seen as a cursor)
     * in a shared environment.
     *
     * @throws \Exception
     */
    private function computeTotalItemsToProcess(): int
    {
        $this->familyReader->rewind();

        $familyCodes = [];
        $totalProductsToProcess = 0;
        while (true) {
            try {
                $familyItem = $this->familyReader->read();
                if (null === $familyItem) {
                    break;
                }
            } catch (InvalidItemException $e) {
                continue;
            }
            $familyCodes[] = $familyItem['code'];

            if (\count($familyCodes) % 100 === 0) {
                $totalProductsToProcess += $this->countProducts($familyCodes);
                $familyCodes = [];
            }
        }
        $totalProductsToProcess += $this->countProducts($familyCodes);

        $this->familyReader->rewind();

        return $totalProductsToProcess;
    }

    private function countProducts(array $familyCodes): int
    {
        $pqb = $this->productQueryBuilderFactory->create(
            [
                'filters' => [
                    [
                        'field'    => 'family',
                        'operator' => Operators::IN_LIST,
                        'value'    => $familyCodes
                    ]
                ]
            ]
        );

        return $pqb->execute()->count();
    }
}
