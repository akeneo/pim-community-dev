<?php

namespace spec\Pim\Component\Connector\Processor\Normalization;

use Akeneo\Component\Batch\Item\DataInvalidItem;
use Akeneo\Component\Batch\Item\ExecutionContext;
use Akeneo\Component\Batch\Job\JobInterface;
use Akeneo\Component\Batch\Job\JobParameters;
use Akeneo\Component\Batch\Model\JobExecution;
use Akeneo\Component\Batch\Model\JobInstance;
use Akeneo\Component\Batch\Model\StepExecution;
use Akeneo\Component\FileStorage\Model\FileInfoInterface;
use Akeneo\Component\StorageUtils\Detacher\ObjectDetacherInterface;
use Akeneo\Component\StorageUtils\Updater\ObjectUpdaterInterface;
use Doctrine\Common\Collections\ArrayCollection;
use PhpSpec\ObjectBehavior;
use Pim\Component\Catalog\Model\AttributeInterface;
use Pim\Component\Catalog\Model\GroupInterface;
use Pim\Component\Catalog\Model\ProductTemplateInterface;
use Pim\Component\Catalog\Model\ProductValueCollectionInterface;
use Pim\Component\Catalog\Model\ProductValueInterface;
use Pim\Component\Connector\Processor\BulkMediaFetcher;
use Prophecy\Argument;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

class VariantGroupProcessorSpec extends ObjectBehavior
{
    function let(
        NormalizerInterface $normalizer,
        ObjectDetacherInterface $objectDetacher,
        BulkMediaFetcher $mediaFetcher,
        StepExecution $stepExecution
    ) {
        $this->beConstructedWith($normalizer, $objectDetacher, $mediaFetcher);
        $this->setStepExecution($stepExecution);
    }

    function it_is_initializable()
    {
        $this->shouldHaveType('\Pim\Component\Connector\Processor\Normalization\VariantGroupProcessor');
    }

    function it_is_an_item_processor()
    {
        $this->shouldImplement('\Akeneo\Component\Batch\Item\ItemProcessorInterface');
    }

    function it_processes_variant_group_without_product_template(
        $objectDetacher,
        $normalizer,
        $stepExecution,
        $mediaFetcher,
        AttributeInterface $color,
        AttributeInterface $weight,
        GroupInterface $variantGroup,
        JobParameters $jobParameters
    ) {
        $stepExecution->getJobParameters()->willReturn($jobParameters);
        $jobParameters->has('with_media')->willReturn(true);
        $jobParameters->get('with_media')->willReturn(false);

        $variantGroup->getProductTemplate()->willReturn(null);
        $variantGroup->getCode()->willReturn('my_variant_group');
        $color->getCode()->willReturn('color');
        $weight->getCode()->willReturn('weight');
        $variantGroup->getAxisAttributes()->willReturn(new ArrayCollection([$color, $weight]));

        $variantStandard = [
            'code' => 'my_variant_group',
            'axis' => ['color', 'weight'],
            'type' => 'variant',
            'labels' => [
                'en_US' => 'My variant group',
                'fr_FR' => 'Mon groupe de variante',
            ]
        ];

        $normalizer->normalize(
            $variantGroup,
            null,
            [
                'with_variant_group_values' => true,
                'identifier'                => 'my_variant_group',
            ]
        )->willReturn($variantStandard);

        $mediaFetcher->fetchAll(Argument::any())->shouldNotBeCalled();

        $this->process($variantGroup)->shouldReturn($variantStandard);

        $objectDetacher->detach($variantGroup)->shouldBeCalled();
    }

    function it_processes_variant_group_without_media(
        $objectDetacher,
        $normalizer,
        $stepExecution,
        $mediaFetcher,
        ProductValueCollectionInterface $emptyCollection,
        GroupInterface $variantGroup,
        ProductTemplateInterface $productTemplate,
        ProductValueInterface $productValue,
        JobParameters $jobParameters,
        JobExecution $jobExecution,
        JobInstance $jobInstance,
        ExecutionContext $executionContext
    ) {
        $stepExecution->getJobParameters()->willReturn($jobParameters);
        $jobParameters->get('filePath')->willReturn('my/path/variant_group.csv');
        $jobParameters->has('with_media')->willReturn(true);
        $jobParameters->get('with_media')->willReturn(true);

        $variantGroup->getProductTemplate()->willReturn($productTemplate);
        $variantGroup->getCode()->willReturn('my_variant_group');

        $productTemplate->getValuesData()->willReturn([$productValue]);

        $stepExecution->getJobExecution()->willReturn($jobExecution);
        $jobExecution->getJobInstance()->willReturn($jobInstance);
        $jobExecution->getId()->willReturn(100);
        $jobInstance->getCode()->willReturn('csv_variant_group_export');

        $jobExecution->getExecutionContext()->willReturn($executionContext);
        $executionContext->get(JobInterface::WORKING_DIRECTORY_PARAMETER)->willReturn('/working/directory/');

        $variantStandard = [
            'code' => 'my_variant_group',
            'values' => [
                'size' => [
                    [
                        'locale' => null,
                        'scope'  => null,
                        'data'   => 'M'
                    ]
                ]
            ]
        ];

        $normalizer->normalize(
            $variantGroup,
            null,
            [
                'with_variant_group_values' => true,
                'identifier'                => 'my_variant_group',
            ]
        )->willReturn($variantStandard);

        $productTemplate->getValuesData()->willReturn($variantStandard['values']);
        $productTemplate->getValues()->willReturn($emptyCollection);
        $mediaFetcher->fetchAll($emptyCollection, '/working/directory/', 'my_variant_group')->shouldBeCalled();
        $mediaFetcher->getErrors()->willReturn([]);

        $this->process($variantGroup)->shouldReturn($variantStandard);

        $objectDetacher->detach($variantGroup)->shouldBeCalled();
    }

    function it_processes_a_variant_group_with_several_media(
        $objectDetacher,
        $normalizer,
        $mediaFetcher,
        $stepExecution,
        ProductValueCollectionInterface $productValueCollection,
        FileInfoInterface $media1,
        FileInfoInterface $media2,
        GroupInterface $variantGroup,
        ProductTemplateInterface $productTemplate,
        ProductValueInterface $productValue1,
        ProductValueInterface $productValue2,
        JobParameters $jobParameters,
        JobExecution $jobExecution,
        JobInstance $jobInstance,
        ExecutionContext $executionContext
    ) {
        $stepExecution->getJobParameters()->willReturn($jobParameters);
        $jobParameters->get('filePath')->willReturn('my/path/variant_group.csv');
        $jobParameters->has('with_media')->willReturn(true);
        $jobParameters->get('with_media')->willReturn(true);

        $variantGroup->getProductTemplate()->willReturn($productTemplate);
        $variantGroup->getCode()->willReturn('my_variant_group');

        $productValueCollection->toArray()->willReturn([$productValue1, $productValue2]);
        $productValue1->getData()->willReturn($media1);
        $productValue2->getData()->willReturn($media2);

        $stepExecution->getJobExecution()->willReturn($jobExecution);
        $jobExecution->getJobInstance()->willReturn($jobInstance);
        $jobExecution->getId()->willReturn(100);
        $jobInstance->getCode()->willReturn('csv_variant_group_export');

        $jobExecution->getExecutionContext()->willReturn($executionContext);
        $executionContext->get(JobInterface::WORKING_DIRECTORY_PARAMETER)->willReturn('/working/directory/');

        $values = [
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
        ];

        $variantStandard = [
            'code'   => 'my_variant_group',
            'values' => $values,
        ];

        $normalizer->normalize(
            $variantGroup,
            null,
            [
                'with_variant_group_values' => true,
                'identifier'                => 'my_variant_group',
            ]
        )->willReturn($variantStandard);

        $productTemplate->getValuesData()->willReturn($variantStandard['values']);
        $productTemplate->getValues()->willReturn($productValueCollection);
        $mediaFetcher->fetchAll($productValueCollection, '/working/directory/', 'my_variant_group')->shouldBeCalled();
        $mediaFetcher->getErrors()->willReturn([]);

        $this->process($variantGroup)->shouldReturn($variantStandard);

        $objectDetacher->detach($variantGroup)->shouldBeCalled();
    }

    function it_throws_an_exception_if_media_of_variant_group_is_not_found(
        $objectDetacher,
        $normalizer,
        $mediaFetcher,
        $stepExecution,
        ProductValueCollectionInterface $productValueCollection,
        FileInfoInterface $media1,
        FileInfoInterface $media2,
        GroupInterface $variantGroup,
        ProductTemplateInterface $productTemplate,
        ProductValueInterface $productValue1,
        ProductValueInterface $productValue2,
        JobParameters $jobParameters,
        JobExecution $jobExecution,
        JobInstance $jobInstance,
        ExecutionContext $executionContext
    ) {
        $stepExecution->getJobParameters()->willReturn($jobParameters);
        $jobParameters->get('filePath')->willReturn('my/path/variant_group.csv');
        $jobParameters->has('with_media')->willReturn(true);
        $jobParameters->get('with_media')->willReturn(true);

        $variantGroup->getProductTemplate()->willReturn($productTemplate);
        $variantGroup->getCode()->willReturn('my_variant_group');

        $productValueCollection->toArray()->willReturn([$productValue1, $productValue2]);
        $productValue1->getData()->willReturn($media1);
        $productValue2->getData()->willReturn($media2);

        $stepExecution->getJobExecution()->willReturn($jobExecution);
        $jobExecution->getJobInstance()->willReturn($jobInstance);
        $jobExecution->getId()->willReturn(100);
        $jobInstance->getCode()->willReturn('csv_variant_group_export');

        $jobExecution->getExecutionContext()->willReturn($executionContext);
        $executionContext->get(JobInterface::WORKING_DIRECTORY_PARAMETER)->willReturn('/working/directory/');

        $values = [
            'picture' => [
                'locale' => null,
                'scope'  => null,
                'data'   => ['filePath' => 'path/not_found.jpg']
            ],
        ];

        $variantStandard = [
            'code'   => 'my_variant_group',
            'values' => $values,
        ];

        $normalizer->normalize(
            $variantGroup,
            null,
            [
                'with_variant_group_values' => true,
                'identifier'                => 'my_variant_group',
            ]
        )->willReturn($variantStandard);

        $productTemplate->getValuesData()->willReturn($variantStandard['values']);
        $productTemplate->getValues()->willReturn($productValueCollection);
        $mediaFetcher->fetchAll($productValueCollection, '/working/directory/', 'my_variant_group')->shouldBeCalled();
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

        $this->process($variantGroup)->shouldReturn($variantStandard);

        $objectDetacher->detach($variantGroup)->shouldBeCalled();
    }
}
