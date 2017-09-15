<?php

namespace spec\Pim\Component\Connector\Processor\Normalization;

use Akeneo\Component\Batch\Item\DataInvalidItem;
use Akeneo\Component\Batch\Item\ExecutionContext;
use Akeneo\Component\Batch\Job\JobInterface;
use Akeneo\Component\Batch\Job\JobParameters;
use Akeneo\Component\Batch\Model\JobExecution;
use Akeneo\Component\Batch\Model\JobInstance;
use Akeneo\Component\Batch\Model\StepExecution;
use Akeneo\Component\StorageUtils\Detacher\ObjectDetacherInterface;
use PhpSpec\ObjectBehavior;
use Pim\Component\Catalog\Model\ProductModelInterface;
use Pim\Component\Catalog\Model\ValueCollectionInterface;
use Pim\Component\Catalog\Repository\AttributeRepositoryInterface;
use Pim\Component\Catalog\ValuesFiller\EntityWithFamilyValuesFillerInterface;
use Pim\Component\Connector\Processor\BulkMediaFetcher;
use Prophecy\Argument;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

class ProductModelProcessorSpec extends ObjectBehavior
{
    function let(
        NormalizerInterface $normalizer,
        AttributeRepositoryInterface $attributeRepository,
        ObjectDetacherInterface $detacher,
        BulkMediaFetcher $mediaFetcher,
        StepExecution $stepExecution,
        EntityWithFamilyValuesFillerInterface $valuesFiller
    ) {
        $this->beConstructedWith(
            $normalizer,
            $attributeRepository,
            $detacher,
            $mediaFetcher,
            $valuesFiller
        );

        $this->setStepExecution($stepExecution);
    }

    function it_is_initializable()
    {
        $this->shouldHaveType('\Pim\Component\Connector\Processor\Normalization\ProductModelProcessor');
    }

    function it_is_an_item_processor()
    {
        $this->shouldImplement('\Akeneo\Component\Batch\Item\ItemProcessorInterface');
    }

    function it_processes_product_model_without_media(
        $detacher,
        $normalizer,
        $stepExecution,
        $mediaFetcher,
        $valuesFiller,
        $attributeRepository,
        ProductModelInterface $productModel,
        JobParameters $jobParameters
    ) {
        $attributeRepository->findMediaAttributeCodes()->willReturn(['picture']);

        $stepExecution->getJobParameters()->willReturn($jobParameters);
        $jobParameters->get('filePath')->willReturn('/my/path/product_model.csv');
        $jobParameters->has('with_media')->willReturn(true);
        $jobParameters->get('with_media')->willReturn(false);

        $valuesFiller->fillMissingValues($productModel)->shouldBeCalled();

        $normalizer->normalize($productModel, 'standard')
            ->willReturn([
                'code'    => 'janis',
                'categories' => ['cat1', 'cat2'],
                'values' => [
                    'picture' => [
                        [
                            'locale' => null,
                            'scope'  => null,
                            'data'   => 'a/b/c/d/e/f/little_cat.jpg'
                        ]
                    ],
                    'size' => [
                        [
                            'locale' => null,
                            'scope'  => null,
                            'data'   => 'M'
                        ]
                    ]
                ]
            ]);

        $mediaFetcher->fetchAll(Argument::cetera())->shouldNotBeCalled();
        $mediaFetcher->getErrors()->shouldNotBeCalled();

        $detacher->detach($productModel)->shouldBeCalled();

        $this->process($productModel)->shouldReturn([
            'code'    => 'janis',
            'categories' => ['cat1', 'cat2'],
            'values' => [
                'size' => [
                    [
                        'locale' => null,
                        'scope'  => null,
                        'data'   => 'M'
                    ]
                ]
            ]
        ]);
    }

    function it_processes_a_product_model_with_several_media(
        $detacher,
        $normalizer,
        $stepExecution,
        $mediaFetcher,
        $valuesFiller,
        ProductModelInterface $productModel,
        JobParameters $jobParameters,
        JobExecution $jobExecution,
        JobInstance $jobInstance,
        ValueCollectionInterface $valuesCollection,
        ExecutionContext $executionContext
    ) {
        $stepExecution->getJobParameters()->willReturn($jobParameters);
        $jobParameters->get('filePath')->willReturn('/my/path/product_model.csv');
        $jobParameters->has('with_media')->willReturn(true);
        $jobParameters->get('with_media')->willReturn(true);

        $valuesFiller->fillMissingValues($productModel)->shouldBeCalled();
        $productModel->getCode()->willReturn('janis');
        $productModel->getValues()->willReturn($valuesCollection);

        $stepExecution->getJobExecution()->willReturn($jobExecution);
        $jobExecution->getJobInstance()->willReturn($jobInstance);
        $jobExecution->getId()->willReturn(100);
        $jobInstance->getCode()->willReturn('csv_product_model_export');

        $jobExecution->getExecutionContext()->willReturn($executionContext);
        $executionContext->get(JobInterface::WORKING_DIRECTORY_PARAMETER)->willReturn('/working/directory/');

        $productModelStandard = [
            'values' => [
                'picture' => [
                    'locale' => null,
                    'scope'  => null,
                    'data'   => ['filePath' => 'a/b/c/d/e/f/little_cat.jpg']
                ],
                'pdf_description' => [
                    'locale' => 'en_US',
                    'scope'  => null,
                    'data'   => ['filePath' => 'a/f/c/c/e/f/little_cat.pdf']
                ]
            ]
        ];

        $normalizer->normalize($productModel, 'standard')
            ->willReturn($productModelStandard);

        $mediaFetcher->fetchAll($valuesCollection, '/working/directory/', 'janis')->shouldBeCalled();
        $mediaFetcher->getErrors()->willReturn([]);

        $this->process($productModel)->shouldReturn($productModelStandard);

        $detacher->detach($productModel)->shouldBeCalled();
    }

    function it_throws_an_exception_if_media_of_product_model_is_not_found(
        $detacher,
        $normalizer,
        $stepExecution,
        $mediaFetcher,
        $valuesFiller,
        ProductModelInterface $productModel,
        JobParameters $jobParameters,
        JobExecution $jobExecution,
        JobInstance $jobInstance,
        ValueCollectionInterface $valuesCollection,
        ExecutionContext $executionContext
    ) {
        $stepExecution->getJobParameters()->willReturn($jobParameters);
        $jobParameters->get('filePath')->willReturn('/my/path/product_model.csv');
        $jobParameters->has('with_media')->willReturn(true);
        $jobParameters->get('with_media')->willReturn(true);

        $valuesFiller->fillMissingValues($productModel)->shouldBeCalled();
        $productModel->getCode()->willReturn('janis');
        $productModel->getValues()->willReturn($valuesCollection);

        $stepExecution->getJobExecution()->willReturn($jobExecution);
        $jobExecution->getJobInstance()->willReturn($jobInstance);
        $jobExecution->getId()->willReturn(100);
        $jobInstance->getCode()->willReturn('csv_product_model_export');

        $jobExecution->getExecutionContext()->willReturn($executionContext);
        $executionContext->get(JobInterface::WORKING_DIRECTORY_PARAMETER)->willReturn('/working/directory/');

        $productModelStandard = [
            'values' => [
                'pdf_description' => [
                    'locale' => 'en_US',
                    'scope'  => null,
                    'data'   => ['filePath' => 'path/not_found.jpg']
                ]
            ]
        ];

        $normalizer->normalize($productModel, 'standard')
            ->willReturn($productModelStandard);

        $mediaFetcher->fetchAll($valuesCollection, '/working/directory/', 'janis')->shouldBeCalled();
        $mediaFetcher->getErrors()->willReturn(
            [
                [
                    'message' => 'The media has not been found or is not currently available',
                    'media'   => ['filePath' => 'path/not_found.jpg']
                ]
            ]
        );

        $stepExecution->addWarning(
            'The media has not been found or is not currently available',
            [],
            new DataInvalidItem(['filePath' => 'path/not_found.jpg'])
        )->shouldBeCalled();

        $this->process($productModel)->shouldReturn($productModelStandard);

        $detacher->detach($productModel)->shouldBeCalled();
    }
}
