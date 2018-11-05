<?php

namespace Specification\Akeneo\Pim\Enrichment\Component\Product\Connector\Processor\MassEdit;

use Akeneo\Tool\Component\Batch\Job\JobParameters;
use Akeneo\Tool\Component\Batch\Model\JobExecution;
use Akeneo\Tool\Component\Batch\Model\StepExecution;
use Akeneo\Tool\Component\StorageUtils\Detacher\ObjectDetacherInterface;
use Akeneo\Tool\Component\StorageUtils\Updater\ObjectUpdaterInterface;
use PhpSpec\ObjectBehavior;
use Akeneo\Pim\Enrichment\Component\Product\Model\ProductInterface;
use Akeneo\Pim\Enrichment\Component\Product\Repository\ProductRepositoryInterface;
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
        $jobParameters->get('actions')->willReturn([[
                'normalized_values' => [
                    'number' => [
                        [
                            'scope' => null,
                            'locale' => null,
                            'data' => '2.5'
                        ]
                    ]
                ],
                'ui_locale'         => 'fr_FR',
                'attribute_locale'  => 'en_US',
                'attribute_channel' => null
            ]]);

        $violations = new ConstraintViolationList([]);
        $validator->validate($product)->willReturn($violations);
        $product->getId()->willReturn(10);

        $productRepository->hasAttributeInFamily(10, 'number')->shouldBeCalled()->willReturn(true);

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
        $jobParameters->get('actions')->willReturn([[
                'normalized_values' => [
                    'categories' => [
                        [
                            'scope' => null,
                            'locale' => null,
                            'data' => ['office', 'bedroom']
                        ]
                    ]
                ],
                'ui_locale'         => 'fr_FR',
                'attribute_locale'  => 'en_US',
                'attribute_channel' => null
            ]]);

        $validator->validate($product)->willReturn($violations);
        $violation = new ConstraintViolation('error2', 'spec', [], '', '', $product);
        $violations = new ConstraintViolationList([$violation, $violation]);
        $validator->validate($product)->willReturn($violations);

        $product->getId()->willReturn(10);
        $productRepository->hasAttributeInFamily(10, 'categories')->shouldBeCalled()->willReturn(true);

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
