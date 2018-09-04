<?php

declare(strict_types=1);

namespace Akeneo\Pim\Enrichment\Component\Product\Job;

use Akeneo\Pim\Enrichment\Component\Product\EntityWithFamilyVariant\KeepOnlyValuesForVariation;
use Akeneo\Pim\Enrichment\Component\Product\Model\EntityWithFamilyVariantInterface;
use Akeneo\Pim\Enrichment\Component\Product\Model\ProductInterface;
use Akeneo\Pim\Enrichment\Component\Product\Model\ProductModel;
use Akeneo\Pim\Enrichment\Component\Product\Model\ProductModelInterface;
use Akeneo\Pim\Enrichment\Component\Product\Repository\ProductModelRepositoryInterface;
use Akeneo\Tool\Component\Batch\Model\StepExecution;
use Akeneo\Tool\Component\Connector\Step\TaskletInterface;
use Akeneo\Tool\Component\StorageUtils\Saver\BulkSaverInterface;
use Doctrine\Common\Persistence\ObjectRepository;
use Doctrine\ORM\EntityRepository;
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

    /**
     * @param EntityRepository                               $familyVariantRepository
     * @param ObjectRepository                               $variantProductRepository
     * @param ProductModelRepositoryInterface                $productModelRepository
     * @param BulkSaverInterface                             $productSaver
     * @param BulkSaverInterface                             $productModelSaver
     * @param KeepOnlyValuesForVariation                     $keepOnlyValuesForVariation
     * @param ValidatorInterface                             $validator
     */
    public function __construct(
        EntityRepository $familyVariantRepository,
        ObjectRepository $variantProductRepository,
        ProductModelRepositoryInterface $productModelRepository,
        BulkSaverInterface $productSaver,
        BulkSaverInterface $productModelSaver,
        KeepOnlyValuesForVariation $keepOnlyValuesForVariation,
        ValidatorInterface $validator
    ) {
        $this->familyVariantRepository = $familyVariantRepository;
        $this->variantProductRepository = $variantProductRepository;
        $this->productModelRepository = $productModelRepository;
        $this->productSaver = $productSaver;
        $this->productModelSaver = $productModelSaver;
        $this->keepOnlyValuesForVariation = $keepOnlyValuesForVariation;
        $this->validator = $validator;
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
            $this->productModelSaver->saveAll($productModels);
        }

        if (!empty($products)) {
            $this->validateProducts($products);
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
