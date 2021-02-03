<?php

namespace Specification\Akeneo\Pim\Enrichment\Component\Product\Connector\Processor\Normalization;

use Akeneo\Pim\Enrichment\Component\Product\Connector\Processor\Normalization\ProductProcessor;
use Akeneo\Pim\Enrichment\Component\Product\Connector\UseCase\GetProductsWithQualityScoresInterface;
use Akeneo\Pim\Enrichment\Component\Product\ValuesFiller\FillMissingValuesInterface;
use Akeneo\Pim\Structure\Component\Model\AttributeInterface;
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
        StepExecution $stepExecution,
        FillMissingValuesInterface $fillMissingProductModelValues,
        GetProductsWithQualityScoresInterface $getProductsWithQualityScores
    ) {
        $this->beConstructedWith(
            $normalizer,
            $channelRepository,
            $attributeRepository,
            $mediaFetcher,
            $fillMissingProductModelValues,
            $getProductsWithQualityScores
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
        ChannelInterface $channel,
        LocaleInterface $locale,
        ProductInterface $product,
        JobParameters $jobParameters,
        AttributeInterface $attribute
    ) {
        $attributeRepository->findMediaAttributeCodes()->willReturn(['picture']);
        $attributeRepository->findOneByIdentifier(Argument::any())->willReturn($attribute);
        $attribute->isLocaleSpecific()->willReturn(false);

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

        $normalizer->normalize($product, 'standard')
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
        ChannelInterface $channel,
        LocaleInterface $locale,
        ProductInterface $product,
        JobParameters $jobParameters,
        JobExecution $jobExecution,
        JobInstance $jobInstance,
        WriteValueCollection $valuesCollection,
        ExecutionContext $executionContext,
        AttributeRepositoryInterface $attributeRepository,
        AttributeInterface $attribute
    ) {

        $attributeRepository->findOneByIdentifier(Argument::any())->willReturn($attribute);
        $attribute->isLocaleSpecific()->willReturn(false);

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
                'picture' => [[
                    'locale' => null,
                    'scope'  => null,
                    'data'   => ['filePath' => 'a/b/c/d/e/f/little_cat.jpg']
                ]],
                'pdf_description' => [[
                    'locale' => 'en_US',
                    'scope'  => null,
                    'data'   => ['filePath' => 'a/f/c/c/e/f/little_cat.pdf']
                ]]
            ]
        ];

        $normalizer->normalize($product, 'standard')->willReturn($productStandard);

        $mediaFetcher->fetchAll($valuesCollection, '/working/directory/', 'AKIS_XS')->shouldBeCalled();
        $mediaFetcher->getErrors()->willReturn([]);

        $this->process($product)->shouldReturn($productStandard);
    }

    function it_throws_an_exception_if_media_of_product_is_not_found(
        $normalizer,
        $channelRepository,
        $stepExecution,
        $mediaFetcher,
        ChannelInterface $channel,
        LocaleInterface $locale,
        ProductInterface $product,
        JobParameters $jobParameters,
        JobExecution $jobExecution,
        JobInstance $jobInstance,
        WriteValueCollection $valuesCollection,
        ExecutionContext $executionContext,
        AttributeRepositoryInterface $attributeRepository,
        AttributeInterface $attribute
    ) {

        $attributeRepository->findOneByIdentifier(Argument::any())->willReturn($attribute);
        $attribute->isLocaleSpecific()->willReturn(false);

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
                'pdf_description' => [[
                    'locale' => 'en_US',
                    'scope'  => null,
                    'data'   => ['filePath' => 'path/not_found.jpg']
                ]]
            ]
        ];

        $normalizer->normalize($product, 'standard')->willReturn($productStandard);

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

    public function it_processes_product_with_filter_on_quality_score(
        $normalizer,
        $channelRepository,
        $stepExecution,
        $mediaFetcher,
        $attributeRepository,
        $getProductsWithQualityScores,
        ChannelInterface $channel,
        LocaleInterface $locale,
        ProductInterface $product,
        JobParameters $jobParameters,
        AttributeInterface $attribute
    ) {
        $product->getIdentifier()->willReturn('a_product');
        $attributeRepository->findMediaAttributeCodes()->willReturn(['picture']);
        $attributeRepository->findOneByIdentifier(Argument::any())->willReturn($attribute);
        $attribute->isLocaleSpecific()->willReturn(false);

        $stepExecution->getJobParameters()->willReturn($jobParameters);
        $jobParameters->get('filePath')->willReturn('/my/path/product.csv');
        $jobParameters->get('filters')->willReturn(
            [
                'structure' => ['scope' => 'mobile', 'locales' => ['en_US', 'fr_FR']],
                'data' => [['field' => 'quality_score_multi_locales']]
            ]
        );
        $jobParameters->has('with_media')->willReturn(true);
        $jobParameters->get('with_media')->willReturn(false);

        $channelRepository->findOneByIdentifier('mobile')->willReturn($channel);
        $channel->getLocales()->willReturn(new ArrayCollection([$locale]));
        $channel->getCode()->willReturn('foobar');
        $channel->getLocaleCodes()->willReturn(['en_US', 'de_DE']);

        $normalizer->normalize($product, 'standard')->willReturn([
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

        $normalizedProductWithQualityScores = [
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
            ],
            'quality_scores' => [
                'mobile' => [
                    'en_US' => 'A',
                    'de_DE' => 'B',
                ]
            ]
        ];

        $getProductsWithQualityScores->fromNormalizedProduct('a_product', Argument::any(), 'mobile', ['en_US', 'fr_FR'])
            ->willReturn($normalizedProductWithQualityScores);

        $this->process($product)->shouldBeLike($normalizedProductWithQualityScores);
    }
}
