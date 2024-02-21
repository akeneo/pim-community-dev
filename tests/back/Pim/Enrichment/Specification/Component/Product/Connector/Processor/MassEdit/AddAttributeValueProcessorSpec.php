<?php

namespace Specification\Akeneo\Pim\Enrichment\Component\Product\Connector\Processor\MassEdit;

use Akeneo\Tool\Component\Batch\Job\JobParameters;
use Akeneo\Tool\Component\Batch\Model\JobExecution;
use Akeneo\Tool\Component\Batch\Model\StepExecution;
use Akeneo\Tool\Component\StorageUtils\Repository\IdentifiableObjectRepositoryInterface;
use Akeneo\Tool\Component\StorageUtils\Updater\PropertyAdderInterface;
use PhpSpec\ObjectBehavior;
use Akeneo\Pim\Enrichment\Component\Product\EntityWithFamilyVariant\CheckAttributeEditable;
use Akeneo\Pim\Structure\Component\Model\AttributeInterface;
use Akeneo\Pim\Enrichment\Component\Product\Model\ProductInterface;
use Symfony\Component\Validator\ConstraintViolationList;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class AddAttributeValueProcessorSpec extends ObjectBehavior
{
    function let(
        ValidatorInterface $productValidator,
        ValidatorInterface $productModelValidator,
        PropertyAdderInterface $propertyAdder,
        IdentifiableObjectRepositoryInterface $attributeRepository,
        CheckAttributeEditable $checkAttributeEditable
    ) {
        $this->beConstructedWith(
            $productValidator,
            $productModelValidator,
            $propertyAdder,
            $attributeRepository,
            $checkAttributeEditable,
            ['pim_catalog_multiselect', 'pim_reference_data_multiselect']
        );
    }

    function it_sets_the_step_execution(StepExecution $stepExecution)
    {
        $this->setStepExecution($stepExecution)->shouldReturn($this);
    }

    function it_does_not_update_the_attribute_if_it_is_not_editable(
        $productValidator,
        $propertyAdder,
        $attributeRepository,
        $checkAttributeEditable,
        ProductInterface $product,
        StepExecution $stepExecution,
        JobExecution $jobExecution,
        JobParameters $jobParameters,
        AttributeInterface $colorsAttribute,
        AttributeInterface $suppliersAttribute
    ) {
        $this->setStepExecution($stepExecution);
        $stepExecution->getJobExecution()->willReturn($jobExecution);

        $stepExecution->getJobParameters()->willReturn($jobParameters);
        $jobParameters->get('filters')->willReturn([]);
        $jobParameters->get('actions')->willReturn([[
            'normalized_values' => [
                'colors' => [
                    [
                        'scope' => null,
                        'locale' => null,
                        'data' => 'blue'
                    ]
                ],
                'suppliers' => [
                    [
                        'scope' => null,
                        'locale' => null,
                        'data' => 'barret'
                    ]
                ]
            ],
            'ui_locale'         => 'fr_FR',
            'attribute_locale'  => 'en_US',
            'attribute_channel' => null
        ]]);

        $attributeRepository->findOneByIdentifier('colors')->willReturn($colorsAttribute);
        $checkAttributeEditable->isEditable($product, $colorsAttribute)->willReturn(true);
        $colorsAttribute->getType()->willReturn('pim_catalog_multiselect');

        $attributeRepository->findOneByIdentifier('suppliers')->willReturn($suppliersAttribute);
        $checkAttributeEditable->isEditable($product, $suppliersAttribute)->willReturn(false);
        $suppliersAttribute->getType()->willReturn('pim_reference_data_multiselect');

        $violations = new ConstraintViolationList([]);
        $productValidator->validate($product)->willReturn($violations);

        $propertyAdder->addData(
            $product,
            'colors',
            'blue',
            ['scope' => null,'locale' => null]
        )->shouldBeCalled();

        $propertyAdder->addData(
            $product,
            'suppliers',
            'barret',
            ['scope' => null,'locale' => null]
        )->shouldNotBeCalled();

        $this->process($product);
    }

    function it_does_not_update_the_attribute_if_it_is_not_a_supported_type(
        $productValidator,
        $propertyAdder,
        $attributeRepository,
        $checkAttributeEditable,
        ProductInterface $product,
        StepExecution $stepExecution,
        JobExecution $jobExecution,
        JobParameters $jobParameters,
        AttributeInterface $colorsAttribute,
        AttributeInterface $suppliersAttribute
    ) {
        $this->setStepExecution($stepExecution);
        $stepExecution->getJobExecution()->willReturn($jobExecution);

        $stepExecution->getJobParameters()->willReturn($jobParameters);
        $jobParameters->get('filters')->willReturn([]);
        $jobParameters->get('actions')->willReturn([[
            'normalized_values' => [
                'colors' => [
                    [
                        'scope' => null,
                        'locale' => null,
                        'data' => 'blue'
                    ]
                ],
                'suppliers' => [
                    [
                        'scope' => null,
                        'locale' => null,
                        'data' => 'barret'
                    ]
                ]
            ],
            'ui_locale'         => 'fr_FR',
            'attribute_locale'  => 'en_US',
            'attribute_channel' => null
        ]]);

        $attributeRepository->findOneByIdentifier('colors')->willReturn($colorsAttribute);
        $checkAttributeEditable->isEditable($product, $colorsAttribute)->willReturn(true);
        $colorsAttribute->getType()->willReturn('pim_catalog_multiselect');

        $attributeRepository->findOneByIdentifier('suppliers')->willReturn($suppliersAttribute);
        $checkAttributeEditable->isEditable($product, $suppliersAttribute)->willReturn(true);
        $suppliersAttribute->getType()->willReturn('pim_reference_data_simpleselect');

        $violations = new ConstraintViolationList([]);
        $productValidator->validate($product)->willReturn($violations);

        $propertyAdder->addData(
            $product,
            'colors',
            'blue',
            ['scope' => null,'locale' => null]
        )->shouldBeCalled();

        $propertyAdder->addData(
            $product,
            'suppliers',
            'barret',
            ['scope' => null,'locale' => null]
        )->shouldNotBeCalled();

        $this->process($product);
    }

    function it_skips_the_entity_if_it_has_nothing_to_update(
        $productValidator,
        $propertyAdder,
        $attributeRepository,
        $checkAttributeEditable,
        ProductInterface $product,
        StepExecution $stepExecution,
        JobExecution $jobExecution,
        JobParameters $jobParameters,
        AttributeInterface $colorsAttribute,
        AttributeInterface $suppliersAttribute
    ) {
        $this->setStepExecution($stepExecution);
        $stepExecution->getJobExecution()->willReturn($jobExecution);

        $stepExecution->getJobParameters()->willReturn($jobParameters);
        $jobParameters->get('filters')->willReturn([]);
        $jobParameters->get('actions')->willReturn([[
            'normalized_values' => [
                'colors' => [
                    [
                        'scope' => null,
                        'locale' => null,
                        'data' => 'blue'
                    ]
                ],
                'suppliers' => [
                    [
                        'scope' => null,
                        'locale' => null,
                        'data' => 'barret'
                    ]
                ]
            ],
            'ui_locale'         => 'fr_FR',
            'attribute_locale'  => 'en_US',
            'attribute_channel' => null
        ]]);

        $attributeRepository->findOneByIdentifier('colors')->willReturn($colorsAttribute);
        $checkAttributeEditable->isEditable($product, $colorsAttribute)->willReturn(false);
        $colorsAttribute->getType()->willReturn('pim_catalog_multiselect');

        $attributeRepository->findOneByIdentifier('suppliers')->willReturn($suppliersAttribute);
        $checkAttributeEditable->isEditable($product, $suppliersAttribute)->willReturn(false);
        $suppliersAttribute->getType()->willReturn('pim_catalog_multiselect');

        $violations = new ConstraintViolationList([]);
        $productValidator->validate($product)->willReturn($violations);

        $propertyAdder->addData(
            $product,
            'colors',
            'blue',
            ['scope' => null,'locale' => null]
        )->shouldNotBeCalled();

        $propertyAdder->addData(
            $product,
            'suppliers',
            'barret',
            ['scope' => null,'locale' => null]
        )->shouldNotBeCalled();

        $stepExecution->incrementSummaryInfo('skipped_products')->shouldBeCalled();

        $this->process($product);
    }

    function it_adds_values_to_attributes(
        $productValidator,
        $propertyAdder,
        $attributeRepository,
        $checkAttributeEditable,
        ProductInterface $product,
        StepExecution $stepExecution,
        JobExecution $jobExecution,
        JobParameters $jobParameters,
        AttributeInterface $colorsAttribute
    ) {
        $this->setStepExecution($stepExecution);
        $stepExecution->getJobExecution()->willReturn($jobExecution);

        $stepExecution->getJobParameters()->willReturn($jobParameters);
        $jobParameters->get('filters')->willReturn([]);
        $jobParameters->get('actions')->willReturn([[
            'normalized_values' => [
                'colors' => [
                    [
                        'scope' => null,
                        'locale' => null,
                        'data' => 'blue'
                    ]
                ]
            ],
            'ui_locale'         => 'fr_FR',
            'attribute_locale'  => 'en_US',
            'attribute_channel' => null
        ]]);

        $attributeRepository->findOneByIdentifier('colors')->willReturn($colorsAttribute);
        $checkAttributeEditable->isEditable($product, $colorsAttribute)->willReturn(true);
        $colorsAttribute->getType()->willReturn('pim_catalog_multiselect');

        $violations = new ConstraintViolationList([]);
        $productValidator->validate($product)->willReturn($violations);

        $propertyAdder->addData(
            $product,
            'colors',
            'blue',
            ['scope' => null,'locale' => null]
        )->shouldBeCalled();

        $this->process($product);
    }
}
