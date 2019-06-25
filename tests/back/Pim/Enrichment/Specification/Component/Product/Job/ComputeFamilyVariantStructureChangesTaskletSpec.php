<?php

namespace Specification\Akeneo\Pim\Enrichment\Component\Product\Job;

use Akeneo\Pim\Enrichment\Component\Product\Query\Filter\Operators;
use Akeneo\Pim\Enrichment\Component\Product\Query\ProductQueryBuilderFactoryInterface;
use Akeneo\Pim\Enrichment\Component\Product\Query\ProductQueryBuilderInterface;
use Akeneo\Tool\Component\Batch\Job\JobParameters;
use Akeneo\Tool\Component\Batch\Model\StepExecution;
use Akeneo\Tool\Component\StorageUtils\Cache\EntityManagerClearerInterface;
use Akeneo\Tool\Component\StorageUtils\Cursor\CursorInterface;
use Akeneo\Tool\Component\StorageUtils\Repository\IdentifiableObjectRepositoryInterface;
use Akeneo\Tool\Component\StorageUtils\Saver\BulkSaverInterface;
use PhpSpec\ObjectBehavior;
use Akeneo\Pim\Enrichment\Component\Product\EntityWithFamilyVariant\KeepOnlyValuesForVariation;
use Akeneo\Pim\Structure\Component\Model\FamilyVariantInterface;
use Akeneo\Pim\Enrichment\Component\Product\Model\ProductModelInterface;
use Akeneo\Pim\Enrichment\Component\Product\Model\ProductInterface;
use Symfony\Component\Validator\ConstraintViolationListInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class ComputeFamilyVariantStructureChangesTaskletSpec extends ObjectBehavior
{
    function let(
        IdentifiableObjectRepositoryInterface $familyVariantRepository,
        ProductQueryBuilderFactoryInterface $productQueryBuilderFactory,
        BulkSaverInterface $productSaver,
        BulkSaverInterface $productModelSaver,
        KeepOnlyValuesForVariation $keepOnlyValuesForVariation,
        EntityManagerClearerInterface $clearer,
        ValidatorInterface $validator
    ) {
        $this->beConstructedWith(
            $familyVariantRepository,
            $productQueryBuilderFactory,
            $productSaver,
            $productModelSaver,
            $keepOnlyValuesForVariation,
            $validator,
            $clearer,
            10
        );
    }

    function it_executes_the_family_variant_structure_computation_on_1_level(
        $familyVariantRepository,
        $productSaver,
        $productModelSaver,
        $keepOnlyValuesForVariation,
        $validator,
        $productQueryBuilderFactory,
        $clearer,
        StepExecution $stepExecution,
        JobParameters $jobParameters,
        FamilyVariantInterface $familyVariant,
        ProductInterface $variantProduct,
        ProductModelInterface $rootProductModel,
        ConstraintViolationListInterface $variantProductViolations,
        ConstraintViolationListInterface $rootProductModelViolations,
        ProductQueryBuilderInterface $pqbVariantProduct,
        CursorInterface $variantProducts,
        ProductQueryBuilderInterface $pqbRootProductModel,
        CursorInterface $rootProductModels
    ) {
        $stepExecution->getJobParameters()->willReturn($jobParameters);
        $jobParameters->get('family_variant_codes')->willReturn(['family_code']);

        $familyVariantRepository->findOneByIdentifier('family_code')->willReturn($familyVariant);
        $familyVariant->getNumberOfLevel()->willReturn(1);

        // Process the variant products
        $productQueryBuilderFactory->create([
            'filters' => [
                ['field' => 'entity_type', 'operator' => Operators::EQUALS, 'value' => ProductInterface::class],
                ['field' => 'family_variant', 'operator' => Operators::IN_LIST, 'value' => ['family_code']],
                ['field' => 'parent', 'operator' => Operators::IS_NOT_EMPTY, 'value' => null]
            ]
        ])->willReturn($pqbVariantProduct);
        $pqbVariantProduct->execute()->willReturn($variantProducts);
        $variantProducts->rewind()->shouldBeCalled();
        $variantProducts->valid()->willReturn(true, false);
        $variantProducts->current()->willReturn($variantProduct);
        $variantProducts->next()->shouldBeCalledTimes(1);
        $keepOnlyValuesForVariation->updateEntitiesWithFamilyVariant([$variantProduct])
            ->shouldBeCalled();
        $validator->validate($variantProduct)->willReturn($variantProductViolations);
        $variantProductViolations->count()->willReturn(0);
        $productSaver->saveAll([$variantProduct])->shouldBeCalled();

        // Process the root product models
        $productQueryBuilderFactory->create([
            'filters' => [
                ['field' => 'entity_type', 'operator' => Operators::EQUALS, 'value' => ProductModelInterface::class],
                ['field' => 'family_variant', 'operator' => Operators::IN_LIST, 'value' => ['family_code']],
                ['field' => 'parent', 'operator' => Operators::IS_EMPTY, 'value' => null]
            ]
        ])->willReturn($pqbRootProductModel);
        $pqbRootProductModel->execute()->willReturn($rootProductModels);
        $rootProductModels->rewind()->shouldBeCalled();
        $rootProductModels->valid()->willReturn(true, false);
        $rootProductModels->current()->willReturn($rootProductModel);
        $rootProductModels->next()->shouldBeCalledTimes(1);
        $keepOnlyValuesForVariation->updateEntitiesWithFamilyVariant([$rootProductModel])
            ->shouldBeCalled();
        $validator->validate($rootProductModel)->willReturn($rootProductModelViolations);
        $rootProductModelViolations->count()->willReturn(0);
        $productModelSaver->saveAll([$rootProductModel])->shouldBeCalled();
        $clearer->clear()->shouldBeCalledTimes(2);

        $this->setStepExecution($stepExecution);
        $this->execute();
    }

    function it_executes_the_family_variant_structure_computation_on_2_levels(
        $familyVariantRepository,
        $productSaver,
        $productModelSaver,
        $keepOnlyValuesForVariation,
        $validator,
        $productQueryBuilderFactory,
        $clearer,
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
        ConstraintViolationListInterface $rootProductModelViolations,
        ProductQueryBuilderInterface $pqbVariantProduct,
        CursorInterface $variantProducts,
        ProductQueryBuilderInterface $pqbSubProductModel,
        CursorInterface $subProductModels,
        ProductQueryBuilderInterface $pqbRootProductModel,
        CursorInterface $rootProductModels
    ) {
        $stepExecution->getJobParameters()->willReturn($jobParameters);
        $jobParameters->get('family_variant_codes')->willReturn(['family_code']);

        $familyVariantRepository->findOneByIdentifier('family_code')->willReturn($familyVariant);
        $familyVariant->getNumberOfLevel()->willReturn(2);
        $familyVariant->getCode()->willReturn('family_code');

        // Process the variant products
        $productQueryBuilderFactory->create([
            'filters' => [
                ['field' => 'entity_type', 'operator' => Operators::EQUALS, 'value' => ProductInterface::class],
                ['field' => 'family_variant', 'operator' => Operators::IN_LIST, 'value' => ['family_code']],
                ['field' => 'parent', 'operator' => Operators::IS_NOT_EMPTY, 'value' => null]
            ]
        ])->willReturn($pqbVariantProduct);
        $pqbVariantProduct->execute()->willReturn($variantProducts);
        $variantProducts->rewind()->shouldBeCalled();
        $variantProducts->valid()->willReturn(true, true, false);
        $variantProducts->current()->willReturn($variantProduct1, $variantProduct2);
        $variantProducts->next()->shouldBeCalledTimes(2);
        $keepOnlyValuesForVariation->updateEntitiesWithFamilyVariant([$variantProduct1])
            ->shouldBeCalled();
        $keepOnlyValuesForVariation->updateEntitiesWithFamilyVariant([$variantProduct2])
            ->shouldBeCalled();
        $validator->validate($variantProduct1)->willReturn($variantProductViolations1);
        $validator->validate($variantProduct2)->willReturn($variantProductViolations2);
        $variantProductViolations1->count()->willReturn(0);
        $variantProductViolations2->count()->willReturn(0);
        $productSaver->saveAll([$variantProduct1, $variantProduct2])->shouldBeCalled();

        // Process the sub product models
        $productQueryBuilderFactory->create([
            'filters' => [
                ['field' => 'entity_type', 'operator' => Operators::EQUALS, 'value' => ProductModelInterface::class],
                ['field' => 'family_variant', 'operator' => Operators::IN_LIST, 'value' => ['family_code']],
                ['field' => 'parent', 'operator' => Operators::IS_NOT_EMPTY, 'value' => null]
            ]
        ])->willReturn($pqbSubProductModel);
        $pqbSubProductModel->execute()->willReturn($subProductModels);
        $subProductModels->rewind()->shouldBeCalled();
        $subProductModels->valid()->willReturn(true, false);
        $subProductModels->current()->willReturn($subProductModel);
        $subProductModels->next()->shouldBeCalledTimes(1);
        $keepOnlyValuesForVariation->updateEntitiesWithFamilyVariant([$subProductModel])
            ->shouldBeCalled();
        $validator->validate($subProductModel)->willReturn($subProductModelViolations);
        $subProductModelViolations->count()->willReturn(0);
        $productModelSaver->saveAll([$subProductModel])->shouldBeCalled();

        // Process the root product models
        $productQueryBuilderFactory->create([
            'filters' => [
                ['field' => 'entity_type', 'operator' => Operators::EQUALS, 'value' => ProductModelInterface::class],
                ['field' => 'family_variant', 'operator' => Operators::IN_LIST, 'value' => ['family_code']],
                ['field' => 'parent', 'operator' => Operators::IS_EMPTY, 'value' => null]
            ]
        ])->willReturn($pqbRootProductModel);
        $pqbRootProductModel->execute()->willReturn($rootProductModels);
        $rootProductModels->rewind()->shouldBeCalled();
        $rootProductModels->valid()->willReturn(true, false);
        $rootProductModels->current()->willReturn($rootProductModel);
        $rootProductModels->next()->shouldBeCalledTimes(1);
        $keepOnlyValuesForVariation->updateEntitiesWithFamilyVariant([$rootProductModel])
            ->shouldBeCalled();
        $validator->validate($rootProductModel)->willReturn($rootProductModelViolations);
        $rootProductModelViolations->count()->willReturn(0);
        $productModelSaver->saveAll([$rootProductModel])->shouldBeCalled();
        $clearer->clear()->shouldBeCalledTimes(3);

        $this->setStepExecution($stepExecution);
        $this->execute();
    }

    function it_throws_an_exception_if_there_is_a_validation_error_on_product(
        $familyVariantRepository,
        $productSaver,
        $keepOnlyValuesForVariation,
        $validator,
        $productQueryBuilderFactory,
        ProductQueryBuilderInterface $pqb,
        CursorInterface $variantProducts,
        StepExecution $stepExecution,
        JobParameters $jobParameters,
        FamilyVariantInterface $familyVariant,
        ProductInterface $variantProduct,
        ConstraintViolationListInterface $variantProductViolations
    ) {
        $stepExecution->getJobParameters()->willReturn($jobParameters);
        $jobParameters->get('family_variant_codes')->willReturn(['family_code']);

        $familyVariantRepository->findOneByIdentifier('family_code')->willReturn($familyVariant);
        $familyVariant->getNumberOfLevel()->willReturn(1);
        $familyVariant->getCode()->willReturn('family_code');

        // Process the variant products
        $productQueryBuilderFactory->create([
            'filters' => [
                ['field' => 'entity_type', 'operator' => Operators::EQUALS, 'value' => ProductInterface::class],
                ['field' => 'family_variant', 'operator' => Operators::IN_LIST, 'value' => ['family_code']],
                ['field' => 'parent', 'operator' => Operators::IS_NOT_EMPTY, 'value' => null]
            ]
        ])->willReturn($pqb);
        $pqb->execute()->willReturn($variantProducts);
        $variantProducts->rewind()->shouldBeCalled();
        $variantProducts->valid()->willReturn(true, false);
        $variantProducts->current()->willReturn($variantProduct);
        $variantProducts->next()->shouldBeCalledTimes(1);
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
        $productSaver,
        $productModelSaver,
        $keepOnlyValuesForVariation,
        $validator,
        $productQueryBuilderFactory,
        StepExecution $stepExecution,
        JobParameters $jobParameters,
        FamilyVariantInterface $familyVariant,
        ProductInterface $variantProduct,
        ProductModelInterface $rootProductModel,
        ConstraintViolationListInterface $variantProductViolations,
        ConstraintViolationListInterface $rootProductModelViolations,
        ProductQueryBuilderInterface $pqbVariantProduct,
        CursorInterface $variantProducts,
        ProductQueryBuilderInterface $pqbRootProductModel,
        CursorInterface $rootProductModels
    ) {
        $stepExecution->getJobParameters()->willReturn($jobParameters);
        $jobParameters->get('family_variant_codes')->willReturn(['family_code']);

        $familyVariantRepository->findOneByIdentifier('family_code')->willReturn($familyVariant);
        $familyVariant->getNumberOfLevel()->willReturn(1);
        $familyVariant->getCode()->willReturn('family_code');

        // Process the variant products
        $productQueryBuilderFactory->create([
            'filters' => [
                ['field' => 'entity_type', 'operator' => Operators::EQUALS, 'value' => ProductInterface::class],
                ['field' => 'family_variant', 'operator' => Operators::IN_LIST, 'value' => ['family_code']],
                ['field' => 'parent', 'operator' => Operators::IS_NOT_EMPTY, 'value' => null]
            ]
        ])->willReturn($pqbVariantProduct);
        $pqbVariantProduct->execute()->willReturn($variantProducts);
        $variantProducts->rewind()->shouldBeCalled();
        $variantProducts->valid()->willReturn(true, false);
        $variantProducts->current()->willReturn($variantProduct);
        $variantProducts->next()->shouldBeCalledTimes(1);
        $keepOnlyValuesForVariation->updateEntitiesWithFamilyVariant([$variantProduct])
            ->shouldBeCalled();
        $validator->validate($variantProduct)->willReturn($variantProductViolations);
        $variantProductViolations->count()->willReturn(0);
        $productSaver->saveAll([$variantProduct])->shouldBeCalled();

        // Process the root product models
        $productQueryBuilderFactory->create([
            'filters' => [
                ['field' => 'entity_type', 'operator' => Operators::EQUALS, 'value' => ProductModelInterface::class],
                ['field' => 'family_variant', 'operator' => Operators::IN_LIST, 'value' => ['family_code']],
                ['field' => 'parent', 'operator' => Operators::IS_EMPTY, 'value' => null]
            ]
        ])->willReturn($pqbRootProductModel);
        $pqbRootProductModel->execute()->willReturn($rootProductModels);
        $rootProductModels->rewind()->shouldBeCalled();
        $rootProductModels->valid()->willReturn(true, false);
        $rootProductModels->current()->willReturn($rootProductModel);
        $rootProductModels->next()->shouldBeCalledTimes(1);
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
        $productSaver,
        $productModelSaver,
        $keepOnlyValuesForVariation,
        $validator,
        $productQueryBuilderFactory,
        $clearer,
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
        ConstraintViolationListInterface $rootProductModelViolations,
        ProductQueryBuilderInterface $pqbVariantProduct,
        CursorInterface $variantProducts,
        ProductQueryBuilderInterface $pqbSubProductModel,
        CursorInterface $subProductModels,
        ProductQueryBuilderInterface $pqbRootProductModel,
        CursorInterface $rootProductModels
    ) {
        $this->beConstructedWith(
            $familyVariantRepository,
            $productQueryBuilderFactory,
            $productSaver,
            $productModelSaver,
            $keepOnlyValuesForVariation,
            $validator,
            $clearer,
            2
        );
        $stepExecution->getJobParameters()->willReturn($jobParameters);
        $jobParameters->get('family_variant_codes')->willReturn(['family_code']);

        $familyVariantRepository->findOneByIdentifier('family_code')->willReturn($familyVariant);
        $familyVariant->getNumberOfLevel()->willReturn(2);
        $familyVariant->getCode()->willReturn('family_code');

        // Process the variant products
        $productQueryBuilderFactory->create([
            'filters' => [
                ['field' => 'entity_type', 'operator' => Operators::EQUALS, 'value' => ProductInterface::class],
                ['field' => 'family_variant', 'operator' => Operators::IN_LIST, 'value' => ['family_code']],
                ['field' => 'parent', 'operator' => Operators::IS_NOT_EMPTY, 'value' => null]
            ]
        ])->willReturn($pqbVariantProduct);
        $pqbVariantProduct->execute()->willReturn($variantProducts);
        $variantProducts->rewind()->shouldBeCalled();
        $variantProducts->valid()->willReturn(true, true, true, true, true, false);
        $variantProducts->current()->willReturn(
            $variantProduct1,
            $variantProduct2,
            $variantProduct3,
            $variantProduct4,
            $variantProduct5
        );
        $variantProducts->next()->shouldBeCalledTimes(5);
        $keepOnlyValuesForVariation->updateEntitiesWithFamilyVariant([$variantProduct1])->shouldBeCalled();
        $keepOnlyValuesForVariation->updateEntitiesWithFamilyVariant([$variantProduct2])->shouldBeCalled();
        $keepOnlyValuesForVariation->updateEntitiesWithFamilyVariant([$variantProduct3])->shouldBeCalled();
        $keepOnlyValuesForVariation->updateEntitiesWithFamilyVariant([$variantProduct4])->shouldBeCalled();
        $keepOnlyValuesForVariation->updateEntitiesWithFamilyVariant([$variantProduct5])->shouldBeCalled();
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
        $productQueryBuilderFactory->create([
            'filters' => [
                ['field' => 'entity_type', 'operator' => Operators::EQUALS, 'value' => ProductModelInterface::class],
                ['field' => 'family_variant', 'operator' => Operators::IN_LIST, 'value' => ['family_code']],
                ['field' => 'parent', 'operator' => Operators::IS_NOT_EMPTY, 'value' => null]
            ]
        ])->willReturn($pqbSubProductModel);
        $pqbSubProductModel->execute()->willReturn($subProductModels);
        $subProductModels->rewind()->shouldBeCalled();
        $subProductModels->valid()->willReturn(true, false);
        $subProductModels->current()->willReturn($subProductModel);
        $subProductModels->next()->shouldBeCalledTimes(1);
        $keepOnlyValuesForVariation->updateEntitiesWithFamilyVariant([$subProductModel])
            ->shouldBeCalled();
        $validator->validate($subProductModel)->willReturn($subProductModelViolations);
        $subProductModelViolations->count()->willReturn(0);
        $productModelSaver->saveAll([$subProductModel])->shouldBeCalled();

        // Process the root product models
        $productQueryBuilderFactory->create([
            'filters' => [
                ['field' => 'entity_type', 'operator' => Operators::EQUALS, 'value' => ProductModelInterface::class],
                ['field' => 'family_variant', 'operator' => Operators::IN_LIST, 'value' => ['family_code']],
                ['field' => 'parent', 'operator' => Operators::IS_EMPTY, 'value' => null]
            ]
        ])->willReturn($pqbRootProductModel);
        $pqbRootProductModel->execute()->willReturn($rootProductModels);
        $rootProductModels->rewind()->shouldBeCalled();
        $rootProductModels->valid()->willReturn(true, true, true, true, true, false);
        $rootProductModels->current()->willReturn(
            $rootProductModel,
            $rootProductModel2,
            $rootProductModel3,
            $rootProductModel4,
            $rootProductModel5
        );
        $rootProductModels->next()->shouldBeCalledTimes(5);
        $keepOnlyValuesForVariation->updateEntitiesWithFamilyVariant([$rootProductModel])->shouldBeCalled();
        $keepOnlyValuesForVariation->updateEntitiesWithFamilyVariant([$rootProductModel2])->shouldBeCalled();
        $keepOnlyValuesForVariation->updateEntitiesWithFamilyVariant([$rootProductModel3])->shouldBeCalled();
        $keepOnlyValuesForVariation->updateEntitiesWithFamilyVariant([$rootProductModel4])->shouldBeCalled();
        $keepOnlyValuesForVariation->updateEntitiesWithFamilyVariant([$rootProductModel5])->shouldBeCalled();

        $validator->validate($rootProductModel)->willReturn($rootProductModelViolations);
        $validator->validate($rootProductModel2)->willReturn($rootProductModelViolations);
        $validator->validate($rootProductModel3)->willReturn($rootProductModelViolations);
        $validator->validate($rootProductModel4)->willReturn($rootProductModelViolations);
        $validator->validate($rootProductModel5)->willReturn($rootProductModelViolations);
        $rootProductModelViolations->count()->willReturn(0);
        $productModelSaver->saveAll([$rootProductModel, $rootProductModel2])->shouldBeCalled();
        $productModelSaver->saveAll([$rootProductModel3, $rootProductModel4])->shouldBeCalled();
        $productModelSaver->saveAll([$rootProductModel5])->shouldBeCalled();
        $clearer->clear()->shouldBeCalledTimes(7);

        $this->setStepExecution($stepExecution);
        $this->execute();
    }
}
