<?php

namespace Specification\Akeneo\Pim\Enrichment\Component\Product\Connector\Processor\MassEdit;

use Akeneo\Pim\Enrichment\Component\Product\Comparator\Filter\FilterInterface;
use Akeneo\Pim\Enrichment\Component\Product\EntityWithFamilyVariant\CheckAttributeEditable;
use Akeneo\Pim\Enrichment\Component\Product\Model\EntityWithFamilyInterface;
use Akeneo\Pim\Enrichment\Component\Product\Model\EntityWithValuesInterface;
use Akeneo\Pim\Enrichment\Component\Product\Model\ProductInterface;
use Akeneo\Pim\Enrichment\Component\Product\Model\ProductModelInterface;
use Akeneo\Pim\Structure\Component\Model\AttributeInterface;
use Akeneo\Tool\Component\Batch\Job\JobParameters;
use Akeneo\Tool\Component\Batch\Model\JobExecution;
use Akeneo\Tool\Component\Batch\Model\StepExecution;
use Akeneo\Tool\Component\StorageUtils\Repository\IdentifiableObjectRepositoryInterface;
use Akeneo\Tool\Component\StorageUtils\Updater\ObjectUpdaterInterface;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;
use Symfony\Component\Validator\ConstraintViolation;
use Symfony\Component\Validator\ConstraintViolationList;
use Symfony\Component\Validator\ConstraintViolationListInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class EditAttributesProcessorSpec extends ObjectBehavior
{
    function let(
        ValidatorInterface $productValidator,
        ValidatorInterface $productModelValidator,
        ObjectUpdaterInterface $productUpdater,
        ObjectUpdaterInterface $productModelUpdater,
        IdentifiableObjectRepositoryInterface $attributeRepository,
        CheckAttributeEditable $checkAttributeEditable,
        FilterInterface $productEmptyValuesFilter,
        FilterInterface $productModelEmptyValuesFilter
    ) {
        $this->beConstructedWith(
            $productValidator,
            $productModelValidator,
            $productUpdater,
            $productModelUpdater,
            $attributeRepository,
            $checkAttributeEditable,
            $productEmptyValuesFilter,
            $productModelEmptyValuesFilter
        );
    }

    function it_sets_the_step_execution(StepExecution $stepExecution)
    {
        $this->setStepExecution($stepExecution)->shouldReturn($this);
    }

    function it_sets_values_to_products_attributes(
        ValidatorInterface $productValidator,
        ObjectUpdaterInterface $productUpdater,
        IdentifiableObjectRepositoryInterface $attributeRepository,
        CheckAttributeEditable $checkAttributeEditable,
        FilterInterface $productEmptyValuesFilter,
        ProductInterface $product,
        StepExecution $stepExecution,
        JobExecution $jobExecution,
        JobParameters $jobParameters,
        AttributeInterface $attribute
    ) {
        $this->setStepExecution($stepExecution);
        $stepExecution->getJobExecution()->willReturn($jobExecution);

        $stepExecution->getJobParameters()->willReturn($jobParameters);
        $jobParameters->get('filters')->willReturn([]);
        $values = [
            'number' => [['scope' => null, 'locale' => null, 'data' => '2.5']],
        ];
        $jobParameters->get('actions')->willReturn([[
            'normalized_values' => $values,
            'ui_locale'         => 'fr_FR',
            'attribute_locale'  => 'en_US',
            'attribute_channel' => null
        ]]);

        $violations = new ConstraintViolationList([]);
        $productValidator->validate($product)->willReturn($violations);
        $product->getId()->willReturn(10);

        $attributeRepository->findOneByIdentifier('number')->willReturn($attribute);
        $checkAttributeEditable->isEditable($product, $attribute)->willReturn(true);

        $productEmptyValuesFilter->filter($product, ['values' => $values])->willReturn(['values' => $values]);

        $productUpdater->update($product, [
            'values' => [
                'number' => [
                    [
                        'scope' => null,
                        'locale' => null,
                        'data' => '2.5'
                    ]
                ]
            ]
        ])->shouldBeCalled();

        $this->process($product)->shouldReturn($product);
    }

    function it_filters_values_to_products_attributes(
        ValidatorInterface $productValidator,
        ObjectUpdaterInterface $productUpdater,
        IdentifiableObjectRepositoryInterface $attributeRepository,
        CheckAttributeEditable $checkAttributeEditable,
        FilterInterface $productEmptyValuesFilter,
        ProductInterface $product,
        StepExecution $stepExecution,
        JobExecution $jobExecution,
        JobParameters $jobParameters,
        AttributeInterface $attribute
    ) {
        $this->setStepExecution($stepExecution);
        $stepExecution->getJobExecution()->willReturn($jobExecution);

        $stepExecution->getJobParameters()->willReturn($jobParameters);
        $jobParameters->get('filters')->willReturn([]);
        $values = [
            'number' => [['scope' => null, 'locale' => null, 'data' => '2.5']],
        ];
        $jobParameters->get('actions')->willReturn([[
            'normalized_values' => $values,
            'ui_locale'         => 'fr_FR',
            'attribute_locale'  => 'en_US',
            'attribute_channel' => null
        ]]);

        $violations = new ConstraintViolationList([]);
        $productValidator->validate($product)->willReturn($violations);
        $product->getId()->willReturn(10);

        $attributeRepository->findOneByIdentifier('number')->willReturn($attribute);
        $checkAttributeEditable->isEditable($product, $attribute)->willReturn(true);

        $productEmptyValuesFilter->filter($product, ['values' => $values])->willReturn(['values' => []]);

        $productUpdater->update($product, Argument::any())->shouldNotBeCalled();

        $stepExecution->incrementSummaryInfo('skipped_products')->shouldBeCalled();

        $this->process($product)->shouldReturn(null);
    }

    function it_skips_invalid_products(
        $productValidator,
        $productUpdater,
        $attributeRepository,
        $checkAttributeEditable,
        FilterInterface $productEmptyValuesFilter,
        ProductInterface $product,
        ConstraintViolationListInterface $violations,
        StepExecution $stepExecution,
        JobExecution $jobExecution,
        JobParameters $jobParameters,
        AttributeInterface $attribute
    ) {
        $this->setStepExecution($stepExecution);

        $stepExecution->getJobExecution()->willReturn($jobExecution);
        $stepExecution->getJobParameters()->willReturn($jobParameters);
        $jobParameters->get('filters')->willReturn([]);
        $values = [
            'categories' => [['scope' => null, 'locale' => null, 'data' => ['office', 'bedroom']]]
        ];
        $jobParameters->get('actions')->willReturn([[
                'normalized_values' => $values,
                'ui_locale'         => 'fr_FR',
                'attribute_locale'  => 'en_US',
                'attribute_channel' => null
            ]]);

        $productValidator->validate($product)->willReturn($violations);
        $violation = new ConstraintViolation('error2', 'spec', [], '', '', $product);
        $violations = new ConstraintViolationList([$violation, $violation]);
        $productValidator->validate($product)->willReturn($violations);

        $product->getId()->willReturn(10);

        $attributeRepository->findOneByIdentifier('categories')->willReturn($attribute);
        $checkAttributeEditable->isEditable($product, $attribute)->willReturn(true);

        $productEmptyValuesFilter->filter($product, ['values' => $values])->willReturn(['values' => $values]);

        $productUpdater->update($product, [
            'values' => [
                'categories' => [
                    [
                        'scope' => null,
                        'locale' => null,
                        'data' => ['office', 'bedroom']
                    ]
                ]
            ]
        ])->shouldBeCalled();

        $this->setStepExecution($stepExecution);
        $stepExecution->addWarning(Argument::cetera())->shouldBeCalled();
        $stepExecution->incrementSummaryInfo('skipped_products')->shouldBeCalled();

        $this->process($product)->shouldReturn(null);
    }

    function it_sets_values_to_product_models_attributes(
        $productModelValidator,
        $productModelUpdater,
        $attributeRepository,
        $checkAttributeEditable,
        FilterInterface $productModelEmptyValuesFilter,
        ProductModelInterface $productModel,
        StepExecution $stepExecution,
        JobExecution $jobExecution,
        JobParameters $jobParameters,
        AttributeInterface $attribute
    ) {
        $this->setStepExecution($stepExecution);
        $stepExecution->getJobExecution()->willReturn($jobExecution);

        $stepExecution->getJobParameters()->willReturn($jobParameters);
        $jobParameters->get('filters')->willReturn([]);
        $values = [
            'number' => [['scope'  => null, 'locale' => null, 'data'   => '2.5']]
        ];
        $jobParameters->get('actions')->willReturn([
            [
                'normalized_values' => $values,
                'ui_locale'         => 'fr_FR',
                'attribute_locale'  => 'en_US',
                'attribute_channel' => null,
            ],
        ]);

        $violations = new ConstraintViolationList([]);
        $productModelValidator->validate($productModel)->willReturn($violations);
        $productModel->getId()->willReturn(10);

        $attributeRepository->findOneByIdentifier('number')->willReturn($attribute);
        $checkAttributeEditable->isEditable($productModel, $attribute)->willReturn(true);

        $productModelEmptyValuesFilter->filter($productModel, ['values' => $values])->willReturn(['values' => $values]);

        $productModelUpdater->update($productModel, [
            'values' => [
                'number' => [
                    [
                        'scope' => null,
                        'locale' => null,
                        'data' => '2.5'
                    ]
                ]
            ]
        ])->shouldBeCalled();

        $this->process($productModel)->shouldReturn($productModel);
    }

    function it_skips_entity_when_attribute_is_not_editable(
        $productModelValidator,
        $productModelUpdater,
        $productValidator,
        $productUpdater,
        $attributeRepository,
        $checkAttributeEditable,
        FilterInterface $productModelEmptyValuesFilter,
        EntityWithFamilyInterface $entityWithFamily,
        ConstraintViolationListInterface $violations,
        StepExecution $stepExecution,
        JobExecution $jobExecution,
        JobParameters $jobParameters,
        AttributeInterface $attribute
    ) {
        $this->setStepExecution($stepExecution);

        $stepExecution->getJobExecution()->willReturn($jobExecution);
        $stepExecution->getJobParameters()->willReturn($jobParameters);
        $jobParameters->get('filters')->willReturn([]);
        $values = [
            'categories' => [['scope'  => null, 'locale' => null, 'data' => ['office', 'bedroom']]],
        ];
        $jobParameters->get('actions')->willReturn([
            [
                'normalized_values' => $values,
                'ui_locale'         => 'fr_FR',
                'attribute_locale'  => 'en_US',
                'attribute_channel' => null,
            ],
        ]);

        $attributeRepository->findOneByIdentifier('categories')->willReturn($attribute);
        $checkAttributeEditable->isEditable($entityWithFamily, $attribute)->willReturn(false);

        $productModelUpdater->update()->shouldNotBeCalled();
        $productUpdater->update()->shouldNotBeCalled();

        $productModelValidator->validate($entityWithFamily)->shouldNotBeCalled();
        $productValidator->validate($entityWithFamily)->shouldNotBeCalled();

        $this->setStepExecution($stepExecution);
        $stepExecution->incrementSummaryInfo('skipped_products')->shouldBeCalled();

        $productModelEmptyValuesFilter->filter($entityWithFamily, ['values' => []])->willReturn(['values' => []]);

        $this->process($entityWithFamily)->shouldReturn(null);
    }
}
