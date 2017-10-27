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
            $levelNumber = $familyVariant->getNumberOfLevel();

            while ($levelNumber >= ProductModel::ROOT_VARIATION_LEVEL) {
                if (ProductModel::ROOT_VARIATION_LEVEL === $levelNumber) {
                    $entitiesWithFamilyVariant = $this->productModelRepository->findRootProductModels($familyVariant);
                } else if ($levelNumber === $familyVariant->getNumberOfLevel()) {
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
