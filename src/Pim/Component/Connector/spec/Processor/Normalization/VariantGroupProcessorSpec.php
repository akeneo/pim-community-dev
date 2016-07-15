<?php

namespace spec\Pim\Component\Connector\Processor\Normalization;

use Akeneo\Component\Batch\Item\FileInvalidItem;
use Akeneo\Component\Batch\Item\InvalidItemException;
use Akeneo\Component\Batch\Job\JobParameters;
use Akeneo\Component\Batch\Model\StepExecution;
use Akeneo\Component\FileStorage\Model\FileInfoInterface;
use Akeneo\Component\StorageUtils\Detacher\ObjectDetacherInterface;
use Doctrine\Common\Collections\ArrayCollection;
use PhpSpec\ObjectBehavior;
use Pim\Component\Catalog\Model\GroupInterface;
use Pim\Component\Catalog\Model\ProductTemplateInterface;
use Pim\Component\Catalog\Model\ProductValueInterface;
use Prophecy\Argument;
use Symfony\Component\HttpFoundation\File\Exception\FileNotFoundException;
use Symfony\Component\Serializer\Normalizer\DenormalizerInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

class VariantGroupProcessorSpec extends ObjectBehavior
{
    function let(
        NormalizerInterface $normalizer,
        DenormalizerInterface $denormalizer,
        StepExecution $stepExecution,
        ObjectDetacherInterface $objectDetacher
    ) {
        $this->beConstructedWith(
            $normalizer,
            $denormalizer,
            $objectDetacher,
            'upload/path/',
            'csv'
        );
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
        GroupInterface $variantGroup,
        JobParameters $jobParameters
    ) {
        $stepExecution->getJobParameters()->willReturn($jobParameters);
        $jobParameters->get('decimalSeparator')->willReturn('.');
        $jobParameters->get('dateFormat')->willReturn('yyyy-MM-dd');

        $variantGroup->getProductTemplate()->willReturn(null);
        $variantGroup->getCode()->willReturn('my_variant_group');

        $normalizer->normalize(
            $variantGroup,
            'csv',
            [
                'with_variant_group_values' => true,
                'identifier'                => 'my_variant_group',
                'decimal_separator'         => '.',
                'date_format'                => 'yyyy-MM-dd',
            ]
        )->willReturn('my;variant;group;to;csv;');

        $this->process($variantGroup)->shouldReturn([
            'media' => [],
            'variant_group' => 'my;variant;group;to;csv;'
        ]);

        $objectDetacher->detach($variantGroup)->shouldBeCalled();
    }

    function it_processes_variant_group_without_media(
        $objectDetacher,
        $normalizer,
        $denormalizer,
        $stepExecution,
        ArrayCollection $productValuesCollection,
        ArrayCollection $emptyCollection,
        GroupInterface $variantGroup,
        ProductTemplateInterface $productTemplate,
        ProductValueInterface $productValue,
        JobParameters $jobParameters
    ) {
        $stepExecution->getJobParameters()->willReturn($jobParameters);
        $jobParameters->get('decimalSeparator')->willReturn('.');
        $jobParameters->get('dateFormat')->willReturn('yyyy-MM-dd');

        $variantGroup->getProductTemplate()->willReturn($productTemplate);
        $variantGroup->getCode()->willReturn('my_variant_group');

        $productTemplate->getValuesData()->willReturn([$productValue]);

        $denormalizer->denormalize([$productValue], 'ProductValue[]', 'json')->willReturn($productValuesCollection);

        $productValuesCollection->filter(Argument::cetera())->willReturn($emptyCollection);
        $emptyCollection->toArray()->willReturn([]);

        $normalizer->normalize(
            $variantGroup,
            'csv',
            [
                'with_variant_group_values' => true,
                'identifier'                => 'my_variant_group',
                'decimal_separator'         => '.',
                'date_format'                => 'yyyy-MM-dd',
            ]
        )->willReturn('my;variant;group;to;csv;');

        $this->process($variantGroup)->shouldReturn([
            'media' => [],
            'variant_group' => 'my;variant;group;to;csv;'
        ]);

        $objectDetacher->detach($variantGroup)->shouldBeCalled();
    }

    function it_processes_a_variant_group_with_several_media(
        $objectDetacher,
        $normalizer,
        $denormalizer,
        $stepExecution,
        ArrayCollection $productValuesCollection,
        ArrayCollection $mediaCollection,
        FileInfoInterface $media1,
        FileInfoInterface $media2,
        GroupInterface $variantGroup,
        ProductTemplateInterface $productTemplate,
        ProductValueInterface $productValue,
        JobParameters $jobParameters
    ) {
        $stepExecution->getJobParameters()->willReturn($jobParameters);
        $jobParameters->get('decimalSeparator')->willReturn('.');
        $jobParameters->get('dateFormat')->willReturn('yyyy-MM-dd');

        $variantGroup->getProductTemplate()->willReturn($productTemplate);
        $variantGroup->getCode()->willReturn('my_variant_group');

        $productTemplate->getValuesData()->willReturn([$productValue]);

        $denormalizer->denormalize([$productValue], 'ProductValue[]', 'json')->willReturn($productValuesCollection);

        $productValuesCollection->filter(Argument::cetera())->willReturn($mediaCollection);
        $mediaCollection->toArray()->willReturn([$media1, $media2]);

        $normalizer->normalize(
            [$media1, $media2],
            'csv',
            [
                'field_name'   => 'media',
                'prepare_copy' => true,
                'identifier'   => 'my_variant_group'
            ]
        )->willReturn([
            ['code' => 'img', 'path' => 'upload/path/', 'ext' => 'jpg'],
            ['code' => 'yolo_img', 'path' => 'upload/path/', 'ext' => 'jpg']
        ]);

        $normalizer->normalize(
            $variantGroup,
            'csv',
            [
                'with_variant_group_values' => true,
                'identifier'                => 'my_variant_group',
                'decimal_separator'         => '.',
                'date_format'                => 'yyyy-MM-dd',
            ]
        )->willReturn('my;variant;group;to;csv;');

        $this->process($variantGroup)->shouldReturn([
            'media' => [
                ['code' => 'img', 'path' => 'upload/path/', 'ext' => 'jpg'],
                ['code' => 'yolo_img', 'path' => 'upload/path/', 'ext' => 'jpg']
            ],
            'variant_group' => 'my;variant;group;to;csv;'
        ]);

        $objectDetacher->detach($variantGroup)->shouldBeCalled();
    }

    function it_throws_an_exception_if_media_of_variant_group_is_not_found(
        $objectDetacher,
        $normalizer,
        $denormalizer,
        $stepExecution,
        ArrayCollection $productValuesCollection,
        ArrayCollection $mediaCollection,
        FileInfoInterface $media,
        GroupInterface $variantGroup,
        ProductTemplateInterface $productTemplate,
        ProductValueInterface $productValue,
        JobParameters $jobParameters
    ) {
        $stepExecution->getJobParameters()->willReturn($jobParameters);
        $jobParameters->get('decimalSeparator')->willReturn('.');
        $jobParameters->get('dateFormat')->willReturn('yyyy-MM-dd');

        $variantGroup->getProductTemplate()->willReturn($productTemplate);
        $variantGroup->getCode()->willReturn('my_variant_group');

        $productTemplate->getValuesData()->willReturn([$productValue]);

        $denormalizer->denormalize([$productValue], 'ProductValue[]', 'json')->willReturn($productValuesCollection);

        $productValuesCollection->filter(Argument::cetera())->willReturn($mediaCollection);
        $mediaCollection->toArray()->willReturn([$media]);

        $normalizer->normalize(
            [$media],
            'csv',
            [
                'field_name'   => 'media',
                'prepare_copy' => true,
                'identifier'   => 'my_variant_group'
            ]
        )->willThrow(new FileNotFoundException('upload/path/img.jpg'));

        $objectDetacher->detach($variantGroup)->shouldBeCalled();

        $this->shouldThrow('Akeneo\Component\Batch\Item\InvalidItemException')->duringProcess($variantGroup);
    }
}
