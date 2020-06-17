<?php

namespace Specification\Akeneo\Pim\Enrichment\Component\Product\Connector\Processor\Normalization;

use Akeneo\Pim\Enrichment\Component\Product\Connector\Processor\Normalization\ProductProcessor;
use Akeneo\Tool\Component\Batch\Item\DataInvalidItem;
use Akeneo\Tool\Component\Batch\Item\ExecutionContext;
use Akeneo\Tool\Component\Batch\Job\JobInterface;
use Akeneo\Tool\Component\Batch\Job\JobParameters;
use Akeneo\Tool\Component\Batch\Model\JobExecution;
use Akeneo\Tool\Component\Batch\Model\JobInstance;
use Akeneo\Tool\Component\Batch\Model\StepExecution;
use Doctrine\Common\Collections\ArrayCollection;
use PhpSpec\ObjectBehavior;
use Akeneo\Channel\Component\Model\ChannelInterface;
use Akeneo\Channel\Component\Model\LocaleInterface;
use Akeneo\Pim\Enrichment\Component\Product\Model\ProductInterface;
use Akeneo\Pim\Enrichment\Component\Product\Model\WriteValueCollection;
use Akeneo\Pim\Structure\Component\Repository\AttributeRepositoryInterface;
use Akeneo\Channel\Component\Repository\ChannelRepositoryInterface;
use Akeneo\Tool\Component\Connector\Processor\BulkMediaFetcher;
use Prophecy\Argument;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

class ProductProcessorSpec extends ObjectBehavior
{
    function let(
        NormalizerInterface $normalizer,
        ChannelRepositoryInterface $channelRepository,
        AttributeRepositoryInterface $attributeRepository,
        BulkMediaFetcher $mediaFetcher,
        StepExecution $stepExecution
    ) {
        $this->beConstructedWith(
            $normalizer,
            $channelRepository,
            $attributeRepository,
            $mediaFetcher
        );

        $this->setStepExecution($stepExecution);
    }

    function it_is_initializable()
    {
        $this->shouldHaveType(
            ProductProcessor::class
        );
    }

    function it_is_an_item_processor()
    {
        $this->shouldImplement('\Akeneo\Tool\Component\Batch\Item\ItemProcessorInterface');
    }

    function it_processes_product_without_media(
        $normalizer,
        $channelRepository,
        $stepExecution,
        $mediaFetcher,
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

        $normalizer->normalize($product, 'standard', ['filter_types' => ['pim.transform.product_value.structured'], 'channels' => ['foobar'], 'locales' => ['en_US']])
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
        $clearer,
        ChannelInterface $channel,
        LocaleInterface $locale,
        ProductInterface $product,
        JobParameters $jobParameters,
        JobExecution $jobExecution,
        JobInstance $jobInstance,
        WriteValueCollection $valuesCollection,
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

        $normalizer->normalize($product, 'standard', ['filter_types' => ['pim.transform.product_value.structured'], 'filter_types' => ['pim.transform.product_value.structured'], 'channels' => ['foobar'], 'locales' => ['en_US']])
            ->willReturn($productStandard);

        $mediaFetcher->fetchAll($valuesCollection, '/working/directory/', 'AKIS_XS')->shouldBeCalled();
        $mediaFetcher->getErrors()->willReturn([]);

        $this->process($product)->shouldReturn($productStandard);
    }

    function it_throws_an_exception_if_media_of_product_is_not_found(
        $normalizer,
        $channelRepository,
        $stepExecution,
        $mediaFetcher,
        $clearer,
        ChannelInterface $channel,
        LocaleInterface $locale,
        ProductInterface $product,
        JobParameters $jobParameters,
        JobExecution $jobExecution,
        JobInstance $jobInstance,
        WriteValueCollection $valuesCollection,
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

        $normalizer->normalize($product, 'standard', ['filter_types' => ['pim.transform.product_value.structured'], 'channels' => ['foobar'], 'locales' => ['en_US']])
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
    }

    function it_does_not_retrieve_value_when_the_attribute_is_locale_specific_and_current_locale_is_not_in_available_list(
        $normalizer,
        $channelRepository,
        $stepExecution,
        ChannelInterface $channel,
        LocaleInterface $locale,
        ProductInterface $product,
        JobParameters $jobParameters,
        JobExecution $jobExecution,
        JobInstance $jobInstance,
        WriteValueCollection $valuesCollection,
        ExecutionContext $executionContext
    ) {
        $stepExecution->getJobParameters()->willReturn($jobParameters);
        $jobParameters->get('filePath')->willReturn('/my/path/product.csv');
        $jobParameters->get('filters')->willReturn(
            [
                'structure' => ['locales' => 'en_US', 'attributes' => ['sku', 'name', 'weight']]
            ]
        );
        $jobParameters->has('with_media')->willReturn(false);

        $channelRepository->findOneByIdentifier('ecommerce')->willReturn($channel);
        $channel->getLocales()->willReturn(new ArrayCollection([$locale]));
        $channel->getCode()->willReturn('foobar');
        $channel->getLocaleCodes()->willReturn(['en_US', 'de_DE']);

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

        $normalizer->normalize($product, 'standard', ['filter_types' => ['pim.transform.product_value.structured'], 'channels' => ['foobar'], 'locales' => ['en_US']])
            ->willReturn($productStandard);


        $product->areAttributesToFilter($jobParameters)->willReturn(true);
        $product->getAttributesToFilter($jobParameters)->shouldBeCalled();
        $attributesToFilter = $product->getAttributesToFilter($jobParameters);
        $product->filterValues($productStandard['values'], $attributesToFilter)->shouldBeCalled();

        $attribute = [
            'code' => 'name'
        ];
        $product->attributeRepository->findOneByIdentifier('name')->willReturn($attribute);

        $attribute->isLocaleSpecific()->willReturn(true);
        $attribute->getLocaleSpecificCodes()->willReturn(['de_DE']);
        $currentLocale = $stepExecution->getJobParameters()->get('filters')['structure']['locales'];
        in_array($currentLocale, $attribute->getLocaleSpecificCodes())->willReturn(false);

        $this->filterValues($productStandard['values'], $attributesToFilter);
    }
}
