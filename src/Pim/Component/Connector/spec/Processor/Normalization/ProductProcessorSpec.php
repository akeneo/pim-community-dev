<?php

namespace spec\Pim\Component\Connector\Processor\Normalization;

use Akeneo\Component\Batch\Item\DataInvalidItem;
use Akeneo\Component\Batch\Job\JobParameters;
use Akeneo\Component\Batch\Model\JobExecution;
use Akeneo\Component\Batch\Model\JobInstance;
use Akeneo\Component\Batch\Model\StepExecution;
use Akeneo\Component\FileStorage\Model\FileInfoInterface;
use Akeneo\Component\StorageUtils\Detacher\ObjectDetacherInterface;
use Doctrine\Common\Collections\ArrayCollection;
use PhpSpec\ObjectBehavior;
use Pim\Component\Catalog\Builder\ProductBuilderInterface;
use Pim\Component\Catalog\Model\ChannelInterface;
use Pim\Component\Catalog\Model\LocaleInterface;
use Pim\Component\Catalog\Model\ProductInterface;
use Pim\Component\Catalog\Model\ProductTemplateInterface;
use Pim\Component\Catalog\Model\ProductValueInterface;
use Pim\Component\Catalog\Repository\AttributeRepositoryInterface;
use Pim\Component\Catalog\Repository\ChannelRepositoryInterface;
use Pim\Component\Connector\Writer\File\BulkFileExporter;
use Prophecy\Argument;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

class ProductProcessorSpec extends ObjectBehavior
{
    function let(
        NormalizerInterface $normalizer,
        ChannelRepositoryInterface $channelRepository,
        AttributeRepositoryInterface $attributeRepository,
        ProductBuilderInterface $productBuilder,
        ObjectDetacherInterface $detacher,
        BulkFileExporter $mediaExporter,
        StepExecution $stepExecution
    ) {
        $this->beConstructedWith(
            $normalizer,
            $channelRepository,
            $attributeRepository,
            $productBuilder,
            $detacher,
            $mediaExporter
        );

        $this->setStepExecution($stepExecution);
    }

    function it_is_initializable()
    {
        $this->shouldHaveType('\Pim\Component\Connector\Processor\Normalization\ProductProcessor');
    }

    function it_is_an_item_processor()
    {
        $this->shouldImplement('\Akeneo\Component\Batch\Item\ItemProcessorInterface');
    }

    function it_processes_product_without_media(
        $detacher,
        $normalizer,
        $channelRepository,
        $stepExecution,
        $mediaExporter,
        $productBuilder,
        ChannelInterface $channel,
        LocaleInterface $locale,
        ProductInterface $product,
        JobParameters $jobParameters
    ) {
        $stepExecution->getJobParameters()->willReturn($jobParameters);
        $jobParameters->get('filePath')->willReturn('/my/path/product.csv');
        $jobParameters->get('filters')->willReturn(
            [
                'structure' => ['scope' => 'mobile', 'locales' => ['en_US', 'fr_FR']]
            ]
        );
        $jobParameters->has('with_media')->willReturn(true);
        $jobParameters->get('with_media')->willReturn(false);

        $channelRepository->findOneByIdentifier('mobile')->willReturn($channel);
        $channel->getLocales()->willReturn(new ArrayCollection([$locale]));
        $channel->getCode()->willReturn('foobar');
        $channel->getLocaleCodes()->willReturn(['en_US', 'de_DE']);

        $productBuilder->addMissingProductValues($product, [$channel], [$locale])->shouldBeCalled();

        $productStandard = [
            'enabled'    => true,
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
        ];

        $normalizer->normalize($product, 'json', ['channels' => 'foobar', 'locales' => ['en_US']])
            ->willReturn($productStandard);

        $mediaExporter->exportAll(Argument::cetera())->shouldNotBeCalled();
        $mediaExporter->getErrors()->shouldNotBeCalled();

        $this->process($product)->shouldReturn($productStandard);

        $detacher->detach($product)->shouldBeCalled();
    }

    function it_processes_a_product_with_several_media(
        $detacher,
        $normalizer,
        $channelRepository,
        $stepExecution,
        $mediaExporter,
        $productBuilder,
        ChannelInterface $channel,
        LocaleInterface $locale,
        ProductInterface $product,
        JobParameters $jobParameters,
        JobExecution $jobExecution,
        JobInstance $jobInstance,
        ProductValueInterface $identifier,
        ArrayCollection $valuesCollection
    ) {
        $stepExecution->getJobParameters()->willReturn($jobParameters);
        $jobParameters->get('filePath')->willReturn('/my/path/product.csv');
        $jobParameters->get('filters')->willReturn(
            [
                'structure' => ['scope' => 'mobile', 'locales' => ['en_US', 'fr_FR']]
            ]
        );
        $jobParameters->has('with_media')->willReturn(true);
        $jobParameters->get('with_media')->willReturn(true);

        $channelRepository->findOneByIdentifier('mobile')->willReturn($channel);
        $channel->getLocales()->willReturn(new ArrayCollection([$locale]));
        $channel->getCode()->willReturn('foobar');
        $channel->getLocaleCodes()->willReturn(['en_US', 'de_DE']);

        $productBuilder->addMissingProductValues($product, [$channel], [$locale])->shouldBeCalled();
        $product->getIdentifier()->willReturn($identifier);
        $product->getValues()->willReturn($valuesCollection);
        $identifier->getData()->willReturn('AKIS_XS');

        $stepExecution->getJobExecution()->willReturn($jobExecution);
        $jobExecution->getJobInstance()->willReturn($jobInstance);
        $jobExecution->getId()->willReturn(100);
        $jobInstance->getCode()->willReturn('csv_product_export');
        $directory = '/my/path/csv_product_export/100/';

        $productStandard = [
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

        $normalizer->normalize($product, 'json', ['channels' => 'foobar', 'locales' => ['en_US']])
            ->willReturn($productStandard);

        $mediaExporter->exportAll($valuesCollection, $directory, 'AKIS_XS')->shouldBeCalled();
        $mediaExporter->getErrors()->willReturn([]);

        $this->process($product)->shouldReturn($productStandard);

        $detacher->detach($product)->shouldBeCalled();
    }

    function it_throws_an_exception_if_media_of_product_is_not_found(
        $detacher,
        $normalizer,
        $channelRepository,
        $stepExecution,
        $mediaExporter,
        $productBuilder,
        ChannelInterface $channel,
        LocaleInterface $locale,
        ProductInterface $product,
        JobParameters $jobParameters,
        JobExecution $jobExecution,
        JobInstance $jobInstance,
        ProductValueInterface $identifier,
        ArrayCollection $valuesCollection
    ) {
        $stepExecution->getJobParameters()->willReturn($jobParameters);
        $jobParameters->get('filePath')->willReturn('/my/path/product.csv');
        $jobParameters->get('filters')->willReturn(
            [
                'structure' => ['scope' => 'mobile', 'locales' => ['en_US', 'fr_FR']]
            ]
        );
        $jobParameters->has('with_media')->willReturn(true);
        $jobParameters->get('with_media')->willReturn(true);

        $channelRepository->findOneByIdentifier('mobile')->willReturn($channel);
        $channel->getLocales()->willReturn(new ArrayCollection([$locale]));
        $channel->getCode()->willReturn('foobar');
        $channel->getLocaleCodes()->willReturn(['en_US', 'de_DE']);

        $productBuilder->addMissingProductValues($product, [$channel], [$locale])->shouldBeCalled();
        $product->getIdentifier()->willReturn($identifier);
        $product->getValues()->willReturn($valuesCollection);
        $identifier->getData()->willReturn('AKIS_XS');

        $stepExecution->getJobExecution()->willReturn($jobExecution);
        $jobExecution->getJobInstance()->willReturn($jobInstance);
        $jobExecution->getId()->willReturn(100);
        $jobInstance->getCode()->willReturn('csv_product_export');
        $directory = '/my/path/csv_product_export/100/';

        $productStandard = [
            'values' => [
                'pdf_description' => [
                    'locale' => 'en_US',
                    'scope'  => null,
                    'data'   => ['filePath' => 'path/not_found.jpg']
                ]
            ]
        ];

        $normalizer->normalize($product, 'json', ['channels' => 'foobar', 'locales' => ['en_US']])
            ->willReturn($productStandard);

        $mediaExporter->exportAll($valuesCollection, $directory, 'AKIS_XS')->shouldBeCalled();
        $mediaExporter->getErrors()->willReturn(
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

        $this->process($product)->shouldReturn($productStandard);

        $detacher->detach($product)->shouldBeCalled();
    }
}
