<?php

declare(strict_types=1);

namespace Pim\Component\Catalog\Job;

use Akeneo\Component\Batch\Model\StepExecution;
use Akeneo\Component\StorageUtils\Detacher\ObjectDetacherInterface;
use Akeneo\Component\StorageUtils\Saver\SaverInterface;
use Doctrine\Common\Persistence\ObjectRepository;
use Doctrine\ORM\EntityRepository;
use Pim\Component\Catalog\EntityWithFamilyVariant\KeepOnlyValuesForVariation;
use Pim\Component\Catalog\Model\EntityWithFamilyVariantInterface;
use Pim\Component\Catalog\Model\FamilyVariantInterface;
use Pim\Component\Catalog\Model\ProductModelInterface;
use Pim\Component\Catalog\Model\VariantAttributeSetInterface;
use Pim\Component\Catalog\Repository\ProductModelRepositoryInterface;
use Pim\Component\Connector\Step\TaskletInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;

/**
 * @author    Adrien Pétremann <adrien.petremann@akeneo.com>
 * @copyright 2017 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 */
class ComputeVariantStructureChangesTasklet implements TaskletInterface
{
    /** @var StepExecution */
    private $stepExecution;

    /** @var EntityRepository */
    private $familyVariantRepository;

    /** @var ObjectRepository */
    private $variantProductRepository;

    /** @var ProductModelRepositoryInterface */
    private $productModelRepository;

    /** @var SaverInterface */
    private $productSaver;

    /** @var SaverInterface */
    private $productModelSaver;

    /** @var KeepOnlyValuesForVariation */
    private $keepOnlyValuesForVariation;

    /** @var ValidatorInterface */
    private $validator;

    /** @var ObjectDetacherInterface */
    private $objectDetacher;

    /**
     * @param EntityRepository                               $familyVariantRepository
     * @param ObjectRepository                               $variantProductRepository
     * @param ProductModelRepositoryInterface                $productModelRepository
     * @param SaverInterface                                 $productSaver
     * @param SaverInterface                                 $productModelSaver
     * @param KeepOnlyValuesForVariation                     $keepOnlyValuesForVariation
     * @param ValidatorInterface                             $validator
     * @param ObjectDetacherInterface                        $objectDetacher
     */
    public function __construct(
        EntityRepository $familyVariantRepository,
        ObjectRepository $variantProductRepository,
        ProductModelRepositoryInterface $productModelRepository,
        SaverInterface $productSaver,
        SaverInterface $productModelSaver,
        KeepOnlyValuesForVariation $keepOnlyValuesForVariation,
        ValidatorInterface $validator,
        ObjectDetacherInterface $objectDetacher
    ) {
        $this->familyVariantRepository = $familyVariantRepository;
        $this->variantProductRepository = $variantProductRepository;
        $this->productModelRepository = $productModelRepository;
        $this->productSaver = $productSaver;
        $this->productModelSaver = $productModelSaver;
        $this->keepOnlyValuesForVariation = $keepOnlyValuesForVariation;
        $this->validator = $validator;
        $this->objectDetacher = $objectDetacher;
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
            $attributeSets = $this->getSortedAttributeSetsByLevel($familyVariant);

            foreach ($attributeSets as $attributeSet) {
                if ($attributeSet->getLevel() === $familyVariant->getNumberOfLevel()) {
                    $entities = $this->variantProductRepository->findBy(['familyVariant' => $familyVariant]);
                } else {
                    $entities = $this->productModelRepository->findSubProductModels($familyVariant);
                }

                $this->updateValues($entities);
            }

            $entities = $this->productModelRepository->findRootProductModels($familyVariant);
            $this->updateValues($entities);
        }
    }

    /**
     * Get sorted attribute sets by level.
     * It returns the attribute sets from low level to top level, so, level 2 first, then level 1...
     *
     * We need to sort to fetch variant product first, then sub product models, then root, in that order.
     *
     * @param FamilyVariantInterface $familyVariant
     *
     * @return array
     */
    private function getSortedAttributeSetsByLevel(FamilyVariantInterface $familyVariant): array
    {
        $attributeSets = $familyVariant->getVariantAttributeSets()->toArray();

        usort($attributeSets, function (
            VariantAttributeSetInterface $a,
            VariantAttributeSetInterface $b
        ) {
            if ($a->getLevel() === $b->getLevel()) {
                return 0;
            }

            return ($a->getLevel() > $b->getLevel()) ? -1 : 1;
        });

        return $attributeSets;
    }

    /**
     * @param EntityWithFamilyVariantInterface[] $entities
     */
    private function updateValues(array $entities): void
    {
        $this->keepOnlyValuesForVariation->updateEntitiesWithFamilyVariant($entities);

        foreach ($entities as $entity) {
            $violations = $this->validator->validate($entity);

            if ($violations->count() === 0) {
                if ($entity instanceof ProductModelInterface) {
                    $this->productModelSaver->save($entity);
                } else {
                    $this->productSaver->save($entity);
                }
            } else {
                if ($entity instanceof ProductModelInterface) {
                    throw new \LogicException(
                        sprintf(
                            'Validation error for ProductModel with code "%s" during family variant structure change',
                            $entity->getCode()
                        )
                    );
                } else {
                    throw new \LogicException(
                        sprintf(
                            'Validation error for Product with identifier "%s" during family variant structure change',
                            $entity->getIdentifier()
                        )
                    );
                }
            }
        }
    }
}
