<?php

declare(strict_types=1);

namespace Pim\Component\Connector\Job;

use Akeneo\Component\Batch\Item\InitializableInterface;
use Akeneo\Component\Batch\Item\InvalidItemException;
use Akeneo\Component\Batch\Item\ItemReaderInterface;
use Akeneo\Component\Batch\Model\StepExecution;
use Akeneo\Component\StorageUtils\Cache\CacheClearerInterface;
use Akeneo\Component\StorageUtils\Cursor\CursorInterface;
use Akeneo\Component\StorageUtils\Saver\BulkSaverInterface;
use Pim\Component\Catalog\EntityWithFamilyVariant\KeepOnlyValuesForProductModelsTrees;
use Pim\Component\Catalog\EntityWithFamilyVariant\KeepOnlyValuesForVariation;
use Pim\Component\Catalog\Model\EntityWithFamilyVariantInterface;
use Pim\Component\Catalog\Model\FamilyInterface;
use Pim\Component\Catalog\Model\FamilyVariantInterface;
use Pim\Component\Catalog\Model\ProductModelInterface;
use Pim\Component\Catalog\Query\Filter\Operators;
use Pim\Component\Catalog\Query\ProductQueryBuilderFactoryInterface;
use Pim\Component\Catalog\Repository\FamilyRepositoryInterface;
use Pim\Component\Connector\Step\TaskletInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;

/**
 * Foreach line of the file to import we will:
 * - fetch the corresponding family object
 * - fetch all the root product models of this family
 * - save this root product model and all its descendants (in order to such things as recompute completeness for instance)
 *
 * @author    Samir Boulil <samir.boulil@akeneo.com>
 * @copyright 2018 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class ComputeDataRelatedToFamilyVariantsTasklet implements TaskletInterface, InitializableInterface
{
    /** @var StepExecution */
    private $stepExecution;

    /** @var ItemReaderInterface */
    private $familyReader;

    /** @var FamilyRepositoryInterface */
    private $familyRepository;

    /** @var BulkSaverInterface */
    private $productModelSaver;

    /** @var BulkSaverInterface */
    private $productSaver;

    /** @var CacheClearerInterface */
    private $cacheClearer;

    /** @var BulkSaverInterface */
    private $familyVariantSaver;

    /** @var ProductQueryBuilderFactoryInterface */
    private $productQueryBuilderFactory;

    /** @var KeepOnlyValuesForVariation */
    private $keepOnlyValuesForVariation;

    /** @var ValidatorInterface */
    private $validator;

    /**
     * @param FamilyRepositoryInterface           $familyRepository
     * @param ProductQueryBuilderFactoryInterface $productQueryBuilderFactory
     * @param ItemReaderInterface                 $familyReader
     * @param KeepOnlyValuesForProductModelsTrees $keepOnlyValuesForVariation
     * @param ValidatorInterface                  $validator
     * @param BulkSaverInterface                  $productModelSaver
     * @param BulkSaverInterface                  $productSaver
     * @param CacheClearerInterface               $cacheClearer
     */
    public function __construct(
        FamilyRepositoryInterface $familyRepository,
        ProductQueryBuilderFactoryInterface $productQueryBuilderFactory,
        ItemReaderInterface $familyReader,
        KeepOnlyValuesForVariation $keepOnlyValuesForVariation,
        ValidatorInterface $validator,
        BulkSaverInterface $familyVariantSaver,
        BulkSaverInterface $productModelSaver,
        BulkSaverInterface $productSaver,
        CacheClearerInterface $cacheClearer
    ) {
        $this->familyReader = $familyReader;
        $this->familyRepository = $familyRepository;
        $this->productQueryBuilderFactory = $productQueryBuilderFactory;
        $this->productModelSaver = $productModelSaver;
        $this->productSaver = $productSaver;
        $this->cacheClearer = $cacheClearer;
        $this->keepOnlyValuesForVariation = $keepOnlyValuesForVariation;
        $this->familyVariantSaver = $familyVariantSaver;
        $this->validator = $validator;
    }

    /**
     * @param StepExecution $stepExecution
     */
    public function setStepExecution(StepExecution $stepExecution)
    {
        $this->stepExecution = $stepExecution;
    }

    /**
     * Execute the tasklet
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

            foreach ($this->getRootProductModelsForFamily($family) as $rootProductModel) {
                $this->updateProductModelAndDescendants([$rootProductModel]);
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
     * @param FamilyInterface $family
     *
     * @return CursorInterface
     */
    private function getRootProductModelsForFamily(FamilyInterface $family): CursorInterface
    {
        $pqb = $this->productQueryBuilderFactory->create();
        $pqb->addFilter('family', Operators::IN_LIST, [$family->getCode()]);
        $pqb->addFilter('parent', Operators::IS_EMPTY, null);

        return $pqb->execute();
    }

    /**
     * Recursively (upwards) updates, validates each elements of the tree and save them if they are valid.
     *
     * It is important to validate and save the product model tree upward. Starting from the products up to the root
     * product model otherwise we may loose information when moving attribute from the attribute sets in the
     * family variant.
     *
     * @param array $entitiesWithFamilyVariant
     */
    private function updateProductModelAndDescendants(array $entitiesWithFamilyVariant)
    {
        foreach ($entitiesWithFamilyVariant as $entityWithFamilyVariant) {
            if ($entityWithFamilyVariant instanceof ProductModelInterface) {
                if ($entityWithFamilyVariant->hasProductModels()) {
                    $this->updateProductModelAndDescendants(
                        $entityWithFamilyVariant->getProductModels()->toArray()
                    );
                } elseif (!$entityWithFamilyVariant->getProducts()->isEmpty()) {
                    $this->updateProductModelAndDescendants(
                        $entityWithFamilyVariant->getProducts()->toArray()
                    );
                }
            }

            $this->keepOnlyValuesForVariation->updateEntitiesWithFamilyVariant($entitiesWithFamilyVariant);

            if (!$this->isValid($entityWithFamilyVariant)) {
                $this->stepExecution->incrementSummaryInfo('skip');
                continue;
            }

            $this->saveEntity($entityWithFamilyVariant);
            $this->stepExecution->incrementSummaryInfo('process');
        }
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
     * @param EntityWithFamilyVariantInterface $entityWithFamilyVariant
     */
    private function saveEntity(EntityWithFamilyVariantInterface $entityWithFamilyVariant): void
    {
        if ($entityWithFamilyVariant instanceof ProductModelInterface) {
            $this->productModelSaver->saveAll([$entityWithFamilyVariant]);
        } else {
            $this->productSaver->saveAll([$entityWithFamilyVariant]);
        }
    }
}
