<?php

namespace spec\Pim\Component\Catalog\Job;

use Akeneo\Component\Batch\Job\JobParameters;
use Akeneo\Component\Batch\Model\StepExecution;
use Akeneo\Component\StorageUtils\Saver\BulkSaverInterface;
use Doctrine\Common\Persistence\ObjectRepository;
use Doctrine\ORM\EntityRepository;
use PhpSpec\ObjectBehavior;
use Pim\Component\Catalog\EntityWithFamilyVariant\KeepOnlyValuesForVariation;
use Pim\Component\Catalog\Model\FamilyVariantInterface;
use Pim\Component\Catalog\Model\ProductModelInterface;
use Pim\Component\Catalog\Model\ProductInterface;
use Pim\Component\Catalog\Repository\ProductModelRepositoryInterface;
use Symfony\Component\Validator\ConstraintViolationListInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class ComputeFamilyVariantStructureChangesTaskletSpec extends ObjectBehavior
{
    function let(
        EntityRepository $familyVariantRepository,
        ObjectRepository $variantProductRepository,
        ProductModelRepositoryInterface $productModelRepository,
        BulkSaverInterface $productSaver,
        BulkSaverInterface $productModelSaver,
        KeepOnlyValuesForVariation $keepOnlyValuesForVariation,
        ValidatorInterface $validator
    ) {
        $this->beConstructedWith(
            $familyVariantRepository,
            $variantProductRepository,
            $productModelRepository,
            $productSaver,
            $productModelSaver,
            $keepOnlyValuesForVariation,
            $validator,
            10
        );
    }

    function it_executes_the_family_variant_structure_computation_on_1_level(
        $familyVariantRepository,
        $variantProductRepository,
        $productModelRepository,
        $productSaver,
        $productModelSaver,
        $keepOnlyValuesForVariation,
        $validator,
        StepExecution $stepExecution,
        JobParameters $jobParameters,
        FamilyVariantInterface $familyVariant,
        ProductInterface $variantProduct,
        ProductModelInterface $rootProductModel,
        ConstraintViolationListInterface $variantProductViolations,
        ConstraintViolationListInterface $rootProductModelViolations
    ) {
        $stepExecution->getJobParameters()->willReturn($jobParameters);
        $jobParameters->get('family_variant_codes')->willReturn(['tshirt']);

        $familyVariantRepository->findBy(['code' => ['tshirt']])->willReturn([$familyVariant]);
        $familyVariant->getNumberOfLevel()->willReturn(1);

        // Process the variant products
        $variantProductRepository->findBy(['familyVariant' => $familyVariant])
            ->willReturn([$variantProduct]);
        $keepOnlyValuesForVariation->updateEntitiesWithFamilyVariant([$variantProduct])
            ->shouldBeCalled();
        $validator->validate($variantProduct)->willReturn($variantProductViolations);
        $variantProductViolations->count()->willReturn(0);
        $productSaver->saveAll([$variantProduct])->shouldBeCalled();

        // Process the root product models
        $productModelRepository->findRootProductModels($familyVariant)
            ->willReturn([$rootProductModel]);
        $keepOnlyValuesForVariation->updateEntitiesWithFamilyVariant([$rootProductModel])
            ->shouldBeCalled();
        $validator->validate($rootProductModel)->willReturn($rootProductModelViolations);
        $rootProductModelViolations->count()->willReturn(0);
        $productModelSaver->saveAll([$rootProductModel])->shouldBeCalled();

        $this->setStepExecution($stepExecution);
        $this->execute();
    }

    function it_executes_the_family_variant_structure_computation_on_2_levels(
        $familyVariantRepository,
        $variantProductRepository,
        $productModelRepository,
        $productSaver,
        $productModelSaver,
        $keepOnlyValuesForVariation,
        $validator,
        StepExecution $stepExecution,
        JobParameters $jobParameters,
        FamilyVariantInterface $familyVariant,
        ProductInterface $variantProduct1,
        ProductInterface $variantProduct2,
        ProductModelInterface $subProductModel,
        ProductModelInterface $rootProductModel,
        ConstraintViolationListInterface $variantProductViolations1,
        ConstraintViolationListInterface $variantProductViolations2,
        ConstraintViolationListInterface $subProductModelViolations,
        ConstraintViolationListInterface $rootProductModelViolations
    ) {
        $stepExecution->getJobParameters()->willReturn($jobParameters);
        $jobParameters->get('family_variant_codes')->willReturn(['tshirt']);

        $familyVariantRepository->findBy(['code' => ['tshirt']])->willReturn([$familyVariant]);
        $familyVariant->getNumberOfLevel()->willReturn(2);

        // Process the variant products
        $variantProductRepository->findBy(['familyVariant' => $familyVariant])
            ->willReturn([$variantProduct1, $variantProduct2]);
        $keepOnlyValuesForVariation->updateEntitiesWithFamilyVariant([$variantProduct1, $variantProduct2])
            ->shouldBeCalled();
        $validator->validate($variantProduct1)->willReturn($variantProductViolations1);
        $validator->validate($variantProduct2)->willReturn($variantProductViolations2);
        $variantProductViolations1->count()->willReturn(0);
        $variantProductViolations2->count()->willReturn(0);
        $productSaver->saveAll([$variantProduct1, $variantProduct2])->shouldBeCalled();

        // Process the sub product models
        $productModelRepository->findSubProductModels($familyVariant)
            ->willReturn([$subProductModel]);
        $keepOnlyValuesForVariation->updateEntitiesWithFamilyVariant([$subProductModel])
            ->shouldBeCalled();
        $validator->validate($subProductModel)->willReturn($subProductModelViolations);
        $subProductModelViolations->count()->willReturn(0);
        $productModelSaver->saveAll([$subProductModel])->shouldBeCalled();


        // Process the root product models
        $productModelRepository->findRootProductModels($familyVariant)
            ->willReturn([$rootProductModel]);
        $keepOnlyValuesForVariation->updateEntitiesWithFamilyVariant([$rootProductModel])
            ->shouldBeCalled();
        $validator->validate($rootProductModel)->willReturn($rootProductModelViolations);
        $rootProductModelViolations->count()->willReturn(0);
        $productModelSaver->saveAll([$rootProductModel])->shouldBeCalled();

        $this->setStepExecution($stepExecution);
        $this->execute();
    }

    function it_throws_an_exception_if_there_is_a_validation_error_on_product(
        $familyVariantRepository,
        $variantProductRepository,
        $productSaver,
        $keepOnlyValuesForVariation,
        $validator,
        StepExecution $stepExecution,
        JobParameters $jobParameters,
        FamilyVariantInterface $familyVariant,
        ProductInterface $variantProduct,
        ConstraintViolationListInterface $variantProductViolations
    ) {
        $stepExecution->getJobParameters()->willReturn($jobParameters);
        $jobParameters->get('family_variant_codes')->willReturn(['tshirt']);

        $familyVariantRepository->findBy(['code' => ['tshirt']])->willReturn([$familyVariant]);
        $familyVariant->getNumberOfLevel()->willReturn(1);

        // Process the variant products
        $variantProductRepository->findBy(['familyVariant' => $familyVariant])
            ->willReturn([$variantProduct]);
        $keepOnlyValuesForVariation->updateEntitiesWithFamilyVariant([$variantProduct])
            ->shouldBeCalled();
        $validator->validate($variantProduct)->willReturn($variantProductViolations);
        $variantProductViolations->count()->willReturn(1);
        $productSaver->saveAll([$variantProduct])->shouldNotBeCalled();

        $this->setStepExecution($stepExecution);
        $this->shouldThrow(\LogicException::class)->during('execute');
    }

    function it_throws_an_exception_if_there_is_a_validation_error_on_product_model(
        $familyVariantRepository,
        $variantProductRepository,
        $productModelRepository,
        $productSaver,
        $productModelSaver,
        $keepOnlyValuesForVariation,
        $validator,
        StepExecution $stepExecution,
        JobParameters $jobParameters,
        FamilyVariantInterface $familyVariant,
        ProductInterface $variantProduct,
        ProductModelInterface $rootProductModel,
        ConstraintViolationListInterface $variantProductViolations,
        ConstraintViolationListInterface $rootProductModelViolations
    ) {
        $stepExecution->getJobParameters()->willReturn($jobParameters);
        $jobParameters->get('family_variant_codes')->willReturn(['tshirt']);

        $familyVariantRepository->findBy(['code' => ['tshirt']])->willReturn([$familyVariant]);
        $familyVariant->getNumberOfLevel()->willReturn(1);

        // Process the variant products
        $variantProductRepository->findBy(['familyVariant' => $familyVariant])
            ->willReturn([$variantProduct]);
        $keepOnlyValuesForVariation->updateEntitiesWithFamilyVariant([$variantProduct])
            ->shouldBeCalled();
        $validator->validate($variantProduct)->willReturn($variantProductViolations);
        $variantProductViolations->count()->willReturn(0);
        $productSaver->saveAll([$variantProduct])->shouldBeCalled();

        // Process the root product models
        $productModelRepository->findRootProductModels($familyVariant)
            ->willReturn([$rootProductModel]);
        $keepOnlyValuesForVariation->updateEntitiesWithFamilyVariant([$rootProductModel])
            ->shouldBeCalled();
        $validator->validate($rootProductModel)->willReturn($rootProductModelViolations);
        $rootProductModelViolations->count()->willReturn(1);
        $productModelSaver->saveAll([$rootProductModel])->shouldNotBeCalled();

        $this->setStepExecution($stepExecution);
        $this->shouldThrow(\LogicException::class)->during('execute');
    }

    function it_saves_multiple_products_and_product_models(
        $familyVariantRepository,
        $variantProductRepository,
        $productModelRepository,
        $productSaver,
        $productModelSaver,
        $keepOnlyValuesForVariation,
        $validator,
        StepExecution $stepExecution,
        JobParameters $jobParameters,
        FamilyVariantInterface $familyVariant,
        ProductInterface $variantProduct1,
        ProductInterface $variantProduct2,
        ProductInterface $variantProduct3,
        ProductInterface $variantProduct4,
        ProductInterface $variantProduct5,
        ProductModelInterface $subProductModel,
        ProductModelInterface $rootProductModel,
        ProductModelInterface $rootProductModel2,
        ProductModelInterface $rootProductModel3,
        ProductModelInterface $rootProductModel4,
        ProductModelInterface $rootProductModel5,
        ConstraintViolationListInterface $variantProductViolations,
        ConstraintViolationListInterface $subProductModelViolations,
        ConstraintViolationListInterface $rootProductModelViolations
    ) {
        $this->beConstructedWith(
            $familyVariantRepository,
            $variantProductRepository,
            $productModelRepository,
            $productSaver,
            $productModelSaver,
            $keepOnlyValuesForVariation,
            $validator,
            2
        );
        $stepExecution->getJobParameters()->willReturn($jobParameters);
        $jobParameters->get('family_variant_codes')->willReturn(['tshirt']);

        $familyVariantRepository->findBy(['code' => ['tshirt']])->willReturn([$familyVariant]);
        $familyVariant->getNumberOfLevel()->willReturn(2);

        // Process the variant products
        $variantProductRepository->findBy(['familyVariant' => $familyVariant])
            ->willReturn([$variantProduct1, $variantProduct2, $variantProduct3, $variantProduct4, $variantProduct5]);
        $keepOnlyValuesForVariation->updateEntitiesWithFamilyVariant(
            [$variantProduct1, $variantProduct2, $variantProduct3, $variantProduct4, $variantProduct5]
        )->shouldBeCalled();
        $validator->validate($variantProduct1)->willReturn($variantProductViolations);
        $validator->validate($variantProduct2)->willReturn($variantProductViolations);
        $validator->validate($variantProduct3)->willReturn($variantProductViolations);
        $validator->validate($variantProduct4)->willReturn($variantProductViolations);
        $validator->validate($variantProduct5)->willReturn($variantProductViolations);
        $variantProductViolations->count()->willReturn(0);
        $productSaver->saveAll([$variantProduct1, $variantProduct2])->shouldBeCalled();
        $productSaver->saveAll([$variantProduct3, $variantProduct4])->shouldBeCalled();
        $productSaver->saveAll([$variantProduct5])->shouldBeCalled();

        // Process the sub product models
        $productModelRepository->findSubProductModels($familyVariant)
            ->willReturn([$subProductModel]);
        $keepOnlyValuesForVariation->updateEntitiesWithFamilyVariant([$subProductModel])
            ->shouldBeCalled();
        $validator->validate($subProductModel)->willReturn($subProductModelViolations);
        $subProductModelViolations->count()->willReturn(0);
        $productModelSaver->saveAll([$subProductModel])->shouldBeCalled();


        // Process the root product models
        $productModelRepository->findRootProductModels($familyVariant)
            ->willReturn(
                [$rootProductModel, $rootProductModel2, $rootProductModel3, $rootProductModel4, $rootProductModel5]
            );
        $keepOnlyValuesForVariation->updateEntitiesWithFamilyVariant(
            [$rootProductModel, $rootProductModel2, $rootProductModel3, $rootProductModel4, $rootProductModel5]
        )->shouldBeCalled();
        $validator->validate($rootProductModel)->willReturn($rootProductModelViolations);
        $validator->validate($rootProductModel2)->willReturn($rootProductModelViolations);
        $validator->validate($rootProductModel3)->willReturn($rootProductModelViolations);
        $validator->validate($rootProductModel4)->willReturn($rootProductModelViolations);
        $validator->validate($rootProductModel5)->willReturn($rootProductModelViolations);
        $rootProductModelViolations->count()->willReturn(0);
        $productModelSaver->saveAll([$rootProductModel, $rootProductModel2])->shouldBeCalled();
        $productModelSaver->saveAll([$rootProductModel3, $rootProductModel4])->shouldBeCalled();
        $productModelSaver->saveAll([$rootProductModel5])->shouldBeCalled();

        $this->setStepExecution($stepExecution);
        $this->execute();
    }
}
