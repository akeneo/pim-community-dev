<?php

namespace spec\Pim\Bundle\EnrichBundle\Connector\Processor\MassEdit\Product;

use Akeneo\Component\Batch\Job\JobParameters;
use Akeneo\Component\Batch\Model\JobExecution;
use Akeneo\Component\Batch\Model\StepExecution;
use Akeneo\Component\StorageUtils\Detacher\ObjectDetacherInterface;
use Akeneo\Component\StorageUtils\Updater\ObjectUpdaterInterface;
use PhpSpec\ObjectBehavior;
use Pim\Component\Catalog\Model\ProductInterface;
use Pim\Component\Catalog\Model\ProductValueInterface;
use Pim\Component\Catalog\Repository\ProductRepositoryInterface;
use Prophecy\Argument;
use Symfony\Component\Validator\ConstraintViolation;
use Symfony\Component\Validator\ConstraintViolationList;
use Symfony\Component\Validator\ConstraintViolationListInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class EditCommonAttributesProcessorSpec extends ObjectBehavior
{
    function let(
        ValidatorInterface $validator,
        ProductRepositoryInterface $productRepository,
        ObjectUpdaterInterface $productUpdater,
        ObjectDetacherInterface $productDetacher
    ) {
        $this->beConstructedWith(
            $validator,
            $productRepository,
            $productUpdater,
            $productDetacher
        );
    }

    function it_sets_the_step_execution(StepExecution $stepExecution)
    {
        $this->setStepExecution($stepExecution)->shouldReturn($this);
    }

    function it_does_not_set_values_when_attribute_is_not_editable(
        $validator,
        $productUpdater,
        $productDetacher,
        $productRepository,
        ProductInterface $product,
        StepExecution $stepExecution,
        JobExecution $jobExecution,
        JobParameters $jobParameters
    ) {
        $this->setStepExecution($stepExecution);

        $product->getIdentifier()->shouldBeCalled()->willReturn('a_sku');
        $product->getId()->willReturn(10);

        $stepExecution->getJobParameters()->willReturn($jobParameters);
        $jobParameters->get('filters')->willReturn([]);
        $jobParameters->get('actions')->willReturn([
            'normalized_values' => json_encode([
                'categories' => [
                    [
                        'scope' => null,
                        'locale' => null,
                        'data' => ['office', 'bedroom']
                    ]
                ]
            ]),
            'ui_locale'         => 'en_US',
            'attribute_locale'  => 'en_US',
            'attribute_channel' => null
        ]);

        $stepExecution->getJobExecution()->willReturn($jobExecution);
        $stepExecution->incrementSummaryInfo('skipped_products')->shouldBeCalled();
        $stepExecution->addWarning(
            'pim_enrich.mass_edit_action.edit-common-attributes.message.no_valid_attribute',
            [],
            Argument::any()
        )->shouldBeCalled();

        $productDetacher->detach($product)->shouldBeCalled();

        $violations = new ConstraintViolationList([]);
        $validator->validate($product)->willReturn($violations);

        $productRepository->hasAttributeInFamily(10, 'categories')->shouldBeCalled()->willReturn(true);
        $productRepository->hasAttributeInVariantGroup(10, 'categories')->shouldBeCalled()->willReturn(true);
        $productUpdater->update($product, Argument::any())->shouldNotBeCalled();

        $this->process($product)->shouldReturn(null);
    }

    function it_sets_values_to_attributes(
        $validator,
        $productUpdater,
        $productRepository,
        ProductInterface $product,
        StepExecution $stepExecution,
        JobExecution $jobExecution,
        JobParameters $jobParameters
    ) {
        $this->setStepExecution($stepExecution);
        $stepExecution->getJobExecution()->willReturn($jobExecution);

        $stepExecution->getJobParameters()->willReturn($jobParameters);
        $jobParameters->get('filters')->willReturn([]);
        $jobParameters->get('actions')->willReturn([
                'normalized_values' => json_encode([
                    'number' => [
                        [
                            'scope' => null,
                            'locale' => null,
                            'data' => '2.5'
                        ]
                    ]
                ]),
                'ui_locale'         => 'fr_FR',
                'attribute_locale'  => 'en_US',
                'attribute_channel' => null
            ]);

        $violations = new ConstraintViolationList([]);
        $validator->validate($product)->willReturn($violations);
        $product->getId()->willReturn(10);

        $productRepository->hasAttributeInFamily(10, 'number')->shouldBeCalled()->willReturn(true);
        $productRepository->hasAttributeInVariantGroup(10, 'number')->shouldBeCalled()->willReturn(false);

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

        $this->process($product);
    }

    function it_sets_invalid_values_to_attributes(
        $validator,
        $productUpdater,
        $productRepository,
        ProductInterface $product,
        ConstraintViolationListInterface $violations,
        StepExecution $stepExecution,
        JobExecution $jobExecution,
        JobParameters $jobParameters
    ) {
        $this->setStepExecution($stepExecution);

        $stepExecution->getJobExecution()->willReturn($jobExecution);
        $stepExecution->getJobParameters()->willReturn($jobParameters);
        $jobParameters->get('filters')->willReturn([]);
        $jobParameters->get('actions')->willReturn([
                'normalized_values' => json_encode([
                    'categories' => [
                        [
                            'scope' => null,
                            'locale' => null,
                            'data' => ['office', 'bedroom']
                        ]
                    ]
                ]),
                'ui_locale'         => 'fr_FR',
                'attribute_locale'  => 'en_US',
                'attribute_channel' => null
            ]);

        $validator->validate($product)->willReturn($violations);
        $violation = new ConstraintViolation('error2', 'spec', [], '', '', $product);
        $violations = new ConstraintViolationList([$violation, $violation]);
        $validator->validate($product)->willReturn($violations);

        $product->getId()->willReturn(10);
        $productRepository->hasAttributeInFamily(10, 'categories')->shouldBeCalled()->willReturn(true);
        $productRepository->hasAttributeInVariantGroup(10, 'categories')->shouldBeCalled()->willReturn(false);

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

        $this->process($product);
    }
}
