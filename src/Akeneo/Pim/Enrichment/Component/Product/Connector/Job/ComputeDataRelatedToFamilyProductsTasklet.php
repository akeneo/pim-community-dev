<?php

declare(strict_types=1);

namespace Akeneo\Pim\Enrichment\Component\Product\Connector\Job;

use Akeneo\Pim\Enrichment\Component\Product\EntityWithFamilyVariant\KeepOnlyValuesForVariation;
use Akeneo\Pim\Enrichment\Component\Product\Model\EntityWithFamilyVariantInterface;
use Akeneo\Pim\Enrichment\Component\Product\Query\Filter\Operators;
use Akeneo\Pim\Enrichment\Component\Product\Query\ProductQueryBuilderFactoryInterface;
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
    private ?StepExecution $stepExecution;
    private IdentifiableObjectRepositoryInterface $familyRepository;
    private ProductQueryBuilderFactoryInterface $productQueryBuilderFactory;
    private ItemReaderInterface $familyReader;
    private KeepOnlyValuesForVariation $keepOnlyValuesForVariation;
    private ValidatorInterface $validator;
    private BulkSaverInterface $productSaver;
    private JobRepositoryInterface $jobRepository;
    private EntityManagerClearerInterface $cacheClearer;
    private int $batchSize;

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

    public function isTrackable(): bool
    {
        return true;
    }

    /**
     * {@inheritdoc}
     */
    public function execute()
    {
        $this->initialize();
        $familyCodes = $this->extractFamilyCodes();
        if (empty($familyCodes)) {
            return;
        }

        $products = $this->getProductsForFamilies($familyCodes);
        $this->stepExecution->setTotalItems($products->count());

        $skippedProducts = [];
        $productsToSave = [];
        foreach ($products as $product) {
            if ($product->isVariant()) {
                $this->keepOnlyValuesForVariation->updateEntitiesWithFamilyVariant([$product]);

                if (!$this->isValid($product)) {
                    $this->stepExecution->incrementSummaryInfo('skip');
                    $this->stepExecution->incrementProcessedItems(1);

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

    /**
     * {@inheritdoc}
     */
    public function initialize()
    {
        $this->cacheClearer->clear();
    }

    private function isValid(EntityWithFamilyVariantInterface $entityWithFamilyVariant): bool
    {
        $violations = $this->validator->validate($entityWithFamilyVariant);

        return $violations->count() === 0;
    }

    private function saveProducts(array $products): void
    {
        if (empty($products)) {
            return;
        }

        // PIM-9798: Force save the products. For example if the required attributes are updated on the family,
        // the product has not changed but completeness does => Need to update it.
        $this->productSaver->saveAll($products, ['force_save' => true]);
        $this->stepExecution->incrementSummaryInfo('process', count($products));
        $this->stepExecution->incrementProcessedItems(count($products));
        $this->jobRepository->updateStepExecution($this->stepExecution);
    }

    private function getProductsForFamilies(array $familyCodes): CursorInterface
    {
        $pqb = $this->productQueryBuilderFactory->create();
        $pqb->addFilter('family', Operators::IN_LIST, $familyCodes);

        return $pqb->execute();
    }

    private function extractFamilyCodes(): array
    {
        $familyCodes = [];
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

            $familyCodes[] = $family->getCode();
        }

        return $familyCodes;
    }
}
