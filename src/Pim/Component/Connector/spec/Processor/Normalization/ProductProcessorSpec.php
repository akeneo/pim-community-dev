<?php

namespace spec\Pim\Component\Connector\Processor\Normalization;

use Akeneo\Component\Batch\Item\DataInvalidItem;
use Akeneo\Component\Batch\Item\ExecutionContext;
use Akeneo\Component\Batch\Job\JobInterface;
use Akeneo\Component\Batch\Job\JobParameters;
use Akeneo\Component\Batch\Model\JobExecution;
use Akeneo\Component\Batch\Model\JobInstance;
use Akeneo\Component\Batch\Model\StepExecution;
use Akeneo\Component\StorageUtils\Cache\EntityManagerClearerInterface;
use Doctrine\Common\Collections\ArrayCollection;
use PhpSpec\ObjectBehavior;
use Pim\Component\Catalog\Model\ChannelInterface;
use Pim\Component\Catalog\Model\LocaleInterface;
use Pim\Component\Catalog\Model\ProductInterface;
use Pim\Component\Catalog\Model\ValueCollectionInterface;
use Pim\Component\Catalog\Repository\AttributeRepositoryInterface;
use Pim\Component\Catalog\Repository\ChannelRepositoryInterface;
use Pim\Component\Catalog\ValuesFiller\EntityWithFamilyValuesFillerInterface;
use Pim\Component\Connector\Processor\BulkMediaFetcher;
use Prophecy\Argument;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

class ProductProcessorSpec extends ObjectBehavior
{
    function let(
        NormalizerInterface $normalizer,
        ChannelRepositoryInterface $channelRepository,
        AttributeRepositoryInterface $attributeRepository,
        BulkMediaFetcher $mediaFetcher,
        StepExecution $stepExecution,
        EntityWithFamilyValuesFillerInterface $productValuesFiller,
        EntityManagerClearerInterface $clearer
    ) {
        $this->beConstructedWith(
            $normalizer,
            $channelRepository,
            $attributeRepository,
            $mediaFetcher,
            $productValuesFiller,
            $clearer
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
        $normalizer,
        $channelRepository,
        $stepExecution,
        $mediaFetcher,
        $productValuesFiller,
        $attributeRepository,
        $clearer,
        ChannelInterface $channel,
        LocaleInterface $locale,
        ProductInterface $product,
        JobParameters $jobParameters
    ) {
        $attributeRepository->findMediaAttributeCodes()->willReturn(['picture']);

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

        $productValuesFiller->fillMissingValues($product)->shouldBeCalled();

        $normalizer->normalize($product, 'standard', ['channels' => ['foobar'], 'locales' => ['en_US']])
            ->willReturn([
                'enabled'    => true,
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

        $clearer->clear()->shouldBeCalled();

        $this->process($product)->shouldReturn([
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
        ]);
    }

    function it_processes_a_product_with_several_media(
        $normalizer,
        $channelRepository,
        $stepExecution,
        $mediaFetcher,
        $productValuesFiller,
        $clearer,
        ChannelInterface $channel,
        LocaleInterface $locale,
        ProductInterface $product,
        JobParameters $jobParameters,
        JobExecution $jobExecution,
        JobInstance $jobInstance,
        ValueCollectionInterface $valuesCollection,
        ExecutionContext $executionContext
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

        $productValuesFiller->fillMissingValues($product)->shouldBeCalled();
        $product->getIdentifier()->willReturn('AKIS_XS');
        $product->getValues()->willReturn($valuesCollection);

        $stepExecution->getJobExecution()->willReturn($jobExecution);
        $jobExecution->getJobInstance()->willReturn($jobInstance);
        $jobExecution->getId()->willReturn(100);
        $jobInstance->getCode()->willReturn('csv_product_export');

        $jobExecution->getExecutionContext()->willReturn($executionContext);
        $executionContext->get(JobInterface::WORKING_DIRECTORY_PARAMETER)->willReturn('/working/directory/');

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

        $normalizer->normalize($product, 'standard', ['channels' => ['foobar'], 'locales' => ['en_US']])
            ->willReturn($productStandard);

        $mediaFetcher->fetchAll($valuesCollection, '/working/directory/', 'AKIS_XS')->shouldBeCalled();
        $mediaFetcher->getErrors()->willReturn([]);

        $this->process($product)->shouldReturn($productStandard);

        $clearer->clear()->shouldBeCalled();
    }

    function it_throws_an_exception_if_media_of_product_is_not_found(
        $normalizer,
        $channelRepository,
        $stepExecution,
        $mediaFetcher,
        $productValuesFiller,
        $clearer,
        ChannelInterface $channel,
        LocaleInterface $locale,
        ProductInterface $product,
        JobParameters $jobParameters,
        JobExecution $jobExecution,
        JobInstance $jobInstance,
        ValueCollectionInterface $valuesCollection,
        ExecutionContext $executionContext
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

        $productValuesFiller->fillMissingValues($product)->shouldBeCalled();
        $product->getIdentifier()->willReturn('AKIS_XS');
        $product->getValues()->willReturn($valuesCollection);

        $stepExecution->getJobExecution()->willReturn($jobExecution);
        $jobExecution->getJobInstance()->willReturn($jobInstance);
        $jobExecution->getId()->willReturn(100);
        $jobInstance->getCode()->willReturn('csv_product_export');

        $jobExecution->getExecutionContext()->willReturn($executionContext);
        $executionContext->get(JobInterface::WORKING_DIRECTORY_PARAMETER)->willReturn('/working/directory/');

        $productStandard = [
            'values' => [
                'pdf_description' => [
                    'locale' => 'en_US',
                    'scope'  => null,
                    'data'   => ['filePath' => 'path/not_found.jpg']
                ]
            ]
        ];

        $normalizer->normalize($product, 'standard', ['channels' => ['foobar'], 'locales' => ['en_US']])
            ->willReturn($productStandard);

        $mediaFetcher->fetchAll($valuesCollection, '/working/directory/', 'AKIS_XS')->shouldBeCalled();
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

        $this->process($product)->shouldReturn($productStandard);

        $clearer->clear()->shouldBeCalled();
    }
}
