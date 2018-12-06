<?php

declare(strict_types=1);

namespace Pim\Component\Catalog\Job;

use Akeneo\Component\Batch\Model\StepExecution;
use Akeneo\Component\StorageUtils\Saver\BulkSaverInterface;
use Doctrine\Common\Persistence\ObjectRepository;
use Doctrine\ORM\EntityRepository;
use Pim\Component\Catalog\EntityWithFamilyVariant\KeepOnlyValuesForVariation;
use Pim\Component\Catalog\Model\EntityWithFamilyVariantInterface;
use Pim\Component\Catalog\Model\ProductInterface;
use Pim\Component\Catalog\Model\ProductModel;
use Pim\Component\Catalog\Model\ProductModelInterface;
use Pim\Component\Catalog\Repository\ProductModelRepositoryInterface;
use Pim\Component\Connector\Step\TaskletInterface;
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

    /** @var EntityRepository */
    private $familyVariantRepository;

    /** @var ObjectRepository */
    private $variantProductRepository;

    /** @var ProductModelRepositoryInterface */
    private $productModelRepository;

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

    /**
     * @param EntityRepository                               $familyVariantRepository
     * @param ObjectRepository                               $variantProductRepository
     * @param ProductModelRepositoryInterface                $productModelRepository
     * @param BulkSaverInterface                             $productSaver
     * @param BulkSaverInterface                             $productModelSaver
     * @param KeepOnlyValuesForVariation                     $keepOnlyValuesForVariation
     * @param ValidatorInterface                             $validator
     * @param int                                            $batchSize
     */
    public function __construct(
        EntityRepository $familyVariantRepository,
        ObjectRepository $variantProductRepository,
        ProductModelRepositoryInterface $productModelRepository,
        BulkSaverInterface $productSaver,
        BulkSaverInterface $productModelSaver,
        KeepOnlyValuesForVariation $keepOnlyValuesForVariation,
        ValidatorInterface $validator,
        int $batchSize = 100
    ) {
        $this->familyVariantRepository = $familyVariantRepository;
        $this->variantProductRepository = $variantProductRepository;
        $this->productModelRepository = $productModelRepository;
        $this->productSaver = $productSaver;
        $this->productModelSaver = $productModelSaver;
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
        $jobParameters = $this->stepExecution->getJobParameters();
        $familyVariantCodes = $jobParameters->get('family_variant_codes');
        $familyVariants = $this->familyVariantRepository->findBy(['code' => $familyVariantCodes]);

        foreach ($familyVariants as $familyVariant) {
            $levelNumber = $familyVariant->getNumberOfLevel();

            while ($levelNumber >= ProductModel::ROOT_VARIATION_LEVEL) {
                if (ProductModel::ROOT_VARIATION_LEVEL === $levelNumber) {
                    $entitiesWithFamilyVariant = $this->productModelRepository->findRootProductModels($familyVariant);
                } elseif ($levelNumber === $familyVariant->getNumberOfLevel()) {
                    $entitiesWithFamilyVariant = $this->variantProductRepository->findBy([
                        'familyVariant' => $familyVariant
                    ]);
                } else {
                    $entitiesWithFamilyVariant = $this->productModelRepository->findSubProductModels($familyVariant);
                }

                $this->updateValuesOfEntities($entitiesWithFamilyVariant);
                $levelNumber--;
            }
        }
    }

    /**
     * @param EntityWithFamilyVariantInterface[] $entities
     */
    private function updateValuesOfEntities(array $entities): void
    {
        $this->keepOnlyValuesForVariation->updateEntitiesWithFamilyVariant($entities);

        $productModels = $this->filterProductModels($entities);
        $products = $this->filterProducts($entities);

        if (!empty($productModels)) {
            $this->validateProductModels($productModels);
            $this->saveAllProductModels($productModels);
        }

        if (!empty($products)) {
            $this->validateProducts($products);
            $this->saveAllProducts($products);
        }
    }

    private function saveAllProductModels($productModels)
    {
        if (count($productModels) > $this->batchSize) {
            while (count($productModels) > 0) {
                $this->productModelSaver->saveAll(array_splice($productModels, 0, $this->batchSize));
            }
        } else {
            $this->productModelSaver->saveAll($productModels);
        }
    }

    private function saveAllProducts($products)
    {
        if (count($products) > $this->batchSize) {
            while (count($products) > 0) {
                $this->productSaver->saveAll(array_splice($products, 0, $this->batchSize));
            }
        } else {
            $this->productSaver->saveAll($products);
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

    /**
     * Returns only product models from the given array.
     *
     * @param array $entities
     *
     * @return ProductModelInterface[]
     */
    private function filterProductModels(array $entities): array
    {
        return array_values(
            array_filter($entities, function ($item) {
                return $item instanceof ProductModelInterface;
            })
        );
    }

    /**
     * Returns only products from the given array.
     *
     * @param array $entities
     *
     * @return ProductInterface[]
     */
    private function filterProducts(array $entities): array
    {
        return array_values(
            array_filter($entities, function ($item) {
                return $item instanceof ProductInterface;
            })
        );
    }
}
