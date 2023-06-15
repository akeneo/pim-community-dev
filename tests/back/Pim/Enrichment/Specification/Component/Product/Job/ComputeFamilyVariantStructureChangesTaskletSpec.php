<?php

namespace Specification\Akeneo\Pim\Enrichment\Component\Product\Job;

use Akeneo\Pim\Enrichment\Component\Product\EntityWithFamilyVariant\KeepOnlyValuesForVariation;
use Akeneo\Pim\Enrichment\Component\Product\Model\ProductInterface;
use Akeneo\Pim\Enrichment\Component\Product\Model\ProductModelInterface;
use Akeneo\Pim\Enrichment\Component\Product\Query\Filter\Operators;
use Akeneo\Pim\Enrichment\Component\Product\Query\ProductQueryBuilderFactoryInterface;
use Akeneo\Pim\Enrichment\Component\Product\Query\ProductQueryBuilderInterface;
use Akeneo\Pim\Structure\Component\Model\FamilyVariantInterface;
use Akeneo\Tool\Component\Batch\Event\EventInterface;
use Akeneo\Tool\Component\Batch\Event\StepExecutionEvent;
use Akeneo\Tool\Component\Batch\Job\JobParameters;
use Akeneo\Tool\Component\Batch\Model\StepExecution;
use Akeneo\Tool\Component\StorageUtils\Cursor\CursorInterface;
use Akeneo\Tool\Component\StorageUtils\Repository\IdentifiableObjectRepositoryInterface;
use Akeneo\Tool\Component\StorageUtils\Saver\BulkSaverInterface;
use PhpSpec\ObjectBehavior;
use PhpSpec\Wrapper\Collaborator;
use Prophecy\Argument;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\Validator\ConstraintViolationInterface;
use Symfony\Component\Validator\ConstraintViolationList;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class ComputeFamilyVariantStructureChangesTaskletSpec extends ObjectBehavior
{
    function let(
        IdentifiableObjectRepositoryInterface $familyVariantRepository,
        ProductQueryBuilderFactoryInterface $productQueryBuilderFactory,
        BulkSaverInterface $productSaver,
        BulkSaverInterface $productModelSaver,
        KeepOnlyValuesForVariation $keepOnlyValuesForVariation,
        EventDispatcherInterface $eventDispatcher,
        ValidatorInterface $validator,
        StepExecution $stepExecution,
        ProductQueryBuilderInterface $rootProductModelPqb,
        CursorInterface $rootProductModels,
        ProductQueryBuilderInterface $subProductModelPqb,
        CursorInterface $subProductModels,
        ProductQueryBuilderInterface $variantProductPqb,
        CursorInterface $variantProducts,
        FamilyVariantInterface $familyVariant,
    ) {
        $eventDispatcher->dispatch(Argument::type(StepExecutionEvent::class),Argument::type('string'))
            ->willReturn(Argument::type('object'));
        $familyVariantRepository->findOneByIdentifier('family_code')->willReturn($familyVariant);

        $productQueryBuilderFactory->create([
            'filters' => [
                ['field' => 'entity_type', 'operator' => Operators::EQUALS, 'value' => ProductModelInterface::class],
                ['field' => 'family_variant', 'operator' => Operators::IN_LIST, 'value' => ['family_code']],
                ['field' => 'parent', 'operator' => Operators::IS_EMPTY, 'value' => null]
            ]
        ])->willReturn($rootProductModelPqb);
        $rootProductModelPqb->execute()->willReturn($rootProductModels);

        $productQueryBuilderFactory->create([
            'filters' => [
                ['field' => 'entity_type', 'operator' => Operators::EQUALS, 'value' => ProductModelInterface::class],
                ['field' => 'family_variant', 'operator' => Operators::IN_LIST, 'value' => ['family_code']],
                ['field' => 'parent', 'operator' => Operators::IS_NOT_EMPTY, 'value' => null]
            ]
        ])->willReturn($subProductModelPqb);
        $subProductModelPqb->execute()->willReturn($subProductModels);

        $productQueryBuilderFactory->create([
            'filters' => [
                ['field' => 'entity_type', 'operator' => Operators::EQUALS, 'value' => ProductInterface::class],
                ['field' => 'family_variant', 'operator' => Operators::IN_LIST, 'value' => ['family_code']],
                ['field' => 'parent', 'operator' => Operators::IS_NOT_EMPTY, 'value' => null]
            ]
        ])->willReturn($variantProductPqb);
        $variantProductPqb->execute()->willReturn($variantProducts);

        $this->beConstructedWith(
            $familyVariantRepository,
            $productQueryBuilderFactory,
            $productSaver,
            $productModelSaver,
            $keepOnlyValuesForVariation,
            $validator,
            $eventDispatcher,
            10
        );

        $stepExecution->getJobParameters()->willReturn(new JobParameters(['family_variant_codes' => ['family_code']]));
        $this->setStepExecution($stepExecution);
    }

    function it_executes_the_family_variant_structure_computation_on_1_level(
        BulkSaverInterface $productSaver,
        BulkSaverInterface $productModelSaver,
        KeepOnlyValuesForVariation $keepOnlyValuesForVariation,
        ValidatorInterface $validator,
        StepExecution $stepExecution,
        FamilyVariantInterface $familyVariant,
        CursorInterface $variantProducts,
        CursorInterface $rootProductModels,
        EventDispatcherInterface $eventDispatcher,
        ProductInterface $variantProduct,
        ProductModelInterface $rootProductModel,
    ) {
        $familyVariant->getNumberOfLevel()->willReturn(1);

        $this->cursorWillYield($variantProducts, [$variantProduct]);
        $keepOnlyValuesForVariation->updateEntitiesWithFamilyVariant([$variantProduct])->shouldBeCalled();
        $validator->validate($variantProduct)->shouldBeCalled()->willReturn(new ConstraintViolationList());
        $productSaver->saveAll([$variantProduct])->shouldBeCalled();

        $this->cursorWillYield($rootProductModels, [$rootProductModel]);
        $keepOnlyValuesForVariation->updateEntitiesWithFamilyVariant([$rootProductModel])->shouldBeCalled();
        $validator->validate($rootProductModel)->shouldBeCalled()->willReturn(new ConstraintViolationList());
        $productModelSaver->saveAll([$rootProductModel])->shouldBeCalled();

        $stepExecution->incrementSummaryInfo('process', 1)->shouldBeCalledTimes(2);

        $eventDispatcher
            ->dispatch(Argument::type(StepExecutionEvent::class), EventInterface::ITEM_STEP_AFTER_BATCH)
            ->shouldBeCalledTimes(2);

        $this->execute();
    }

    function it_executes_the_family_variant_structure_computation_on_2_levels(
        BulkSaverInterface $productSaver,
        BulkSaverInterface $productModelSaver,
        KeepOnlyValuesForVariation $keepOnlyValuesForVariation,
        ValidatorInterface $validator,
        StepExecution $stepExecution,
        FamilyVariantInterface $familyVariant,
        CursorInterface $variantProducts,
        CursorInterface $subProductModels,
        CursorInterface $rootProductModels,
        EventDispatcherInterface $eventDispatcher,
        ProductInterface $variantProduct,
        ProductModelInterface $subProductModel,
        ProductModelInterface $rootProductModel,
    ) {
        $familyVariant->getNumberOfLevel()->willReturn(2);

        $this->cursorWillYield($variantProducts, [$variantProduct]);
        $keepOnlyValuesForVariation->updateEntitiesWithFamilyVariant([$variantProduct])->shouldBeCalled();
        $validator->validate($variantProduct)->shouldBeCalled()->willReturn(new ConstraintViolationList());
        $productSaver->saveAll([$variantProduct])->shouldBeCalled();

        $this->cursorWillYield($subProductModels, [$subProductModel]);
        $keepOnlyValuesForVariation->updateEntitiesWithFamilyVariant([$subProductModel])->shouldBeCalled();
        $validator->validate($subProductModel)->shouldBeCalled()->willReturn(new ConstraintViolationList());
        $productModelSaver->saveAll([$subProductModel])->shouldBeCalled();

        $this->cursorWillYield($rootProductModels, [$rootProductModel]);
        $keepOnlyValuesForVariation->updateEntitiesWithFamilyVariant([$rootProductModel])->shouldBeCalled();
        $validator->validate($rootProductModel)->shouldBeCalled()->willReturn(new ConstraintViolationList());
        $productModelSaver->saveAll([$rootProductModel])->shouldBeCalled();


        $stepExecution->incrementSummaryInfo('process', 1)->shouldBeCalledTimes(3);

        $eventDispatcher
            ->dispatch(Argument::type(StepExecutionEvent::class), EventInterface::ITEM_STEP_AFTER_BATCH)
            ->shouldBeCalledTimes(3);

        $this->execute();
    }

    function it_skips_invalid_products_and_models_and_saves_the_rest(
        BulkSaverInterface $productSaver,
        BulkSaverInterface $productModelSaver,
        KeepOnlyValuesForVariation $keepOnlyValuesForVariation,
        ValidatorInterface $validator,
        StepExecution $stepExecution,
        FamilyVariantInterface $familyVariant,
        CursorInterface $variantProducts,
        CursorInterface $rootProductModels,
        EventDispatcherInterface $eventDispatcher,
        ProductInterface $variantProduct1,
        ProductInterface $variantProduct2,
        ProductInterface $variantProduct3,
        ProductModelInterface $productModel1,
        ProductModelInterface $productModel2,
        ConstraintViolationInterface $productViolation,
        ConstraintViolationInterface $productModelViolation,
    ) {
        $familyVariant->getNumberOfLevel()->willReturn(1);

        $this->cursorWillYield($variantProducts, [$variantProduct1, $variantProduct2, $variantProduct3]);
        $keepOnlyValuesForVariation->updateEntitiesWithFamilyVariant([$variantProduct1])->shouldBeCalled();
        $keepOnlyValuesForVariation->updateEntitiesWithFamilyVariant([$variantProduct2])->shouldBeCalled();
        $keepOnlyValuesForVariation->updateEntitiesWithFamilyVariant([$variantProduct3])->shouldBeCalled();
        $validator->validate($variantProduct1)->shouldBeCalled()->willReturn(new ConstraintViolationList());
        $validator->validate($variantProduct2)->shouldBeCalled()->willReturn(new ConstraintViolationList([$productViolation->getWrappedObject()]));
        $validator->validate($variantProduct3)->shouldBeCalled()->willReturn(new ConstraintViolationList());
        $productSaver->saveAll([$variantProduct1, $variantProduct3])->shouldBeCalled();
        $stepExecution->incrementSummaryInfo('process', 2)->shouldBeCalled();

        $this->cursorWillYield($rootProductModels, [$productModel1, $productModel2]);
        $keepOnlyValuesForVariation->updateEntitiesWithFamilyVariant([$productModel1])->shouldBeCalled();
        $keepOnlyValuesForVariation->updateEntitiesWithFamilyVariant([$productModel2])->shouldBeCalled();
        $validator->validate($productModel1)->shouldBeCalled()->willReturn(new ConstraintViolationList([$productModelViolation->getWrappedObject()]));
        $validator->validate($productModel2)->shouldBeCalled()->willReturn(new ConstraintViolationList());
        $productModelSaver->saveAll([$productModel2])->shouldBeCalled();
        $stepExecution->incrementSummaryInfo('process', 1)->shouldBeCalled();

        $stepExecution->incrementSummaryInfo('skip')->shouldBeCalledTimes(2);
        $stepExecution->addWarning(Argument::cetera())->shouldBeCalledTimes(2);

        $eventDispatcher
            ->dispatch(Argument::type(StepExecutionEvent::class), EventInterface::ITEM_STEP_AFTER_BATCH)
            ->shouldBeCalledTimes(2);

        $this->execute();
    }

    private function cursorWillYield(Collaborator $cursor, array $yield): void
    {
        $cursor->rewind()->shouldBeCalledOnce();
        $valid = \array_fill(0, \count($yield), true);
        $valid[] = false;
        $cursor->valid()->shouldBeCalledTimes(count($valid))->willReturn(...$valid);
        $cursor->next()->shouldBeCalledTimes(count($yield));
        $cursor->current()->shouldBeCalledTimes(count($yield))->willReturn(...$yield);
    }
}
