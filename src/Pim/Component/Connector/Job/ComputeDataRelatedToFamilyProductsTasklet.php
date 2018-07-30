<?php

declare(strict_types=1);

namespace Pim\Component\Connector\Job;

use Akeneo\Pim\Structure\Component\Model\FamilyInterface;
use Akeneo\Tool\Component\Batch\Item\InitializableInterface;
use Akeneo\Tool\Component\Batch\Item\InvalidItemException;
use Akeneo\Tool\Component\Batch\Item\ItemReaderInterface;
use Akeneo\Tool\Component\Batch\Job\JobRepositoryInterface;
use Akeneo\Tool\Component\Batch\Model\StepExecution;
use Akeneo\Tool\Component\StorageUtils\Cache\EntityManagerClearerInterface;
use Akeneo\Tool\Component\StorageUtils\Cursor\CursorInterface;
use Akeneo\Tool\Component\StorageUtils\Detacher\ObjectDetacherInterface;
use Akeneo\Tool\Component\StorageUtils\Repository\IdentifiableObjectRepositoryInterface;
use Akeneo\Tool\Component\StorageUtils\Saver\BulkSaverInterface;
use Pim\Component\Catalog\EntityWithFamilyVariant\KeepOnlyValuesForVariation;
use Pim\Component\Catalog\Model\EntityWithFamilyVariantInterface;
use Pim\Component\Catalog\Query\Filter\Operators;
use Pim\Component\Catalog\Query\ProductQueryBuilderFactoryInterface;
use Pim\Component\Connector\Step\TaskletInterface;
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
class ComputeDataRelatedToFamilyProductsTasklet implements TaskletInterface, InitializableInterface
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

    /** @var ObjectDetacherInterface */
    private $objectDetacher;

    /** @var int */
    private $batchSize;

    /**
     * @todo merge master: remove the object detacher, the default value from $batchSize
     *                     and the "= null" from the validator and keepOnlyValuesForVariation.
     *
     * @param IdentifiableObjectRepositoryInterface $familyRepository
     * @param ProductQueryBuilderFactoryInterface   $productQueryBuilderFactory
     * @param ItemReaderInterface                   $familyReader
     * @param BulkSaverInterface                    $productSaver
     * @param ObjectDetacherInterface               $objectDetacher
     * @param EntityManagerClearerInterface         $cacheClearer
     * @param JobRepositoryInterface                $jobRepository
     * @param KeepOnlyValuesForVariation            $keepOnlyValuesForVariation
     * @param ValidatorInterface                    $validator
     * @param int                                   $batchSize
     */
    public function __construct(
        IdentifiableObjectRepositoryInterface $familyRepository,
        ProductQueryBuilderFactoryInterface $productQueryBuilderFactory,
        ItemReaderInterface $familyReader,
        BulkSaverInterface $productSaver,
        ObjectDetacherInterface $objectDetacher,
        EntityManagerClearerInterface $cacheClearer,
        JobRepositoryInterface $jobRepository,
        KeepOnlyValuesForVariation $keepOnlyValuesForVariation = null,
        ValidatorInterface $validator = null,
        int $batchSize = 10
    ) {
        $this->familyRepository = $familyRepository;
        $this->productQueryBuilderFactory = $productQueryBuilderFactory;
        $this->familyReader = $familyReader;
        $this->productSaver = $productSaver;
        $this->jobRepository = $jobRepository;
        $this->objectDetacher = $objectDetacher;
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

            $productsToSave = [];
            $products = $this->getProductsForFamily($family);

            foreach ($products as $product) {
                if (null !== $this->keepOnlyValuesForVariation     // TODO merge master: remove these two "null !=="
                    && null !== $this->validator
                    && $product->isVariant()
                ) {
                    $this->keepOnlyValuesForVariation->updateEntitiesWithFamilyVariant([$product]);

                    if (!$this->isValid($product)) {
                        $this->stepExecution->incrementSummaryInfo('skip');
                        continue;
                    }
                }

                $productsToSave[] = $product;

                if (0 === count($productsToSave) % $this->batchSize) {
                    $this->saveProducts($productsToSave);
                    $productsToSave= [];
                    $this->cacheClearer->clear();
                }
            }

            if (!empty($productsToSave)) {
                $this->saveProducts($productsToSave);
            }
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
        $this->productSaver->saveAll($products);
        $this->stepExecution->incrementSummaryInfo('process', count($products));
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
}
