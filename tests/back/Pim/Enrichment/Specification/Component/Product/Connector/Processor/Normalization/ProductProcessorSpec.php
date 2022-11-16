<?php

namespace Specification\Akeneo\Pim\Enrichment\Component\Product\Connector\Processor\Normalization;

use Akeneo\Channel\Infrastructure\Component\Model\ChannelInterface;
use Akeneo\Channel\Infrastructure\Component\Model\LocaleInterface;
use Akeneo\Channel\Infrastructure\Component\Repository\ChannelRepositoryInterface;
use Akeneo\Pim\Enrichment\Component\Product\Connector\Processor\Normalization\GetNormalizedProductQualityScores;
use Akeneo\Pim\Enrichment\Component\Product\Connector\Processor\Normalization\ProductProcessor;
use Akeneo\Pim\Enrichment\Component\Product\Model\ProductInterface;
use Akeneo\Pim\Enrichment\Component\Product\Model\WriteValueCollection;
use Akeneo\Pim\Enrichment\Component\Product\ValuesFiller\FillMissingValuesInterface;
use Akeneo\Pim\Structure\Component\AttributeTypes;
use Akeneo\Pim\Structure\Component\Model\AttributeInterface;
use Akeneo\Pim\Structure\Component\Query\PublicApi\AttributeType\Attribute;
use Akeneo\Pim\Structure\Component\Query\PublicApi\AttributeType\GetAttributes;
use Akeneo\Pim\Structure\Component\Repository\AttributeRepositoryInterface;
use Akeneo\Tool\Component\Batch\Item\ExecutionContext;
use Akeneo\Tool\Component\Batch\Item\ItemProcessorInterface;
use Akeneo\Tool\Component\Batch\Job\JobInterface;
use Akeneo\Tool\Component\Batch\Job\JobParameters;
use Akeneo\Tool\Component\Batch\Model\JobExecution;
use Akeneo\Tool\Component\Batch\Model\JobInstance;
use Akeneo\Tool\Component\Batch\Model\StepExecution;
use Doctrine\Common\Collections\ArrayCollection;
use PhpSpec\ObjectBehavior;
use Ramsey\Uuid\Uuid;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

class ProductProcessorSpec extends ObjectBehavior
{
    function let(
        NormalizerInterface $normalizer,
        ChannelRepositoryInterface $channelRepository,
        AttributeRepositoryInterface $attributeRepository,
        FillMissingValuesInterface $fillMissingProductModelValues,
        GetAttributes $getAttributes,
        GetNormalizedProductQualityScores $getNormalizedProductQualityScores,
        StepExecution $stepExecution
    ) {
        $this->beConstructedWith(
            $normalizer,
            $channelRepository,
            $attributeRepository,
            $fillMissingProductModelValues,
            $getAttributes,
            $getNormalizedProductQualityScores,
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
        $this->shouldImplement(ItemProcessorInterface::class);
    }

    function it_processes_product_without_media(
        NormalizerInterface $normalizer,
        ChannelRepositoryInterface $channelRepository,
        AttributeRepositoryInterface $attributeRepository,
        GetAttributes $getAttributes,
        StepExecution $stepExecution,
        ChannelInterface $channel,
        LocaleInterface $locale,
        ProductInterface $product,
        JobParameters $jobParameters
    ) {
        $attributeRepository->findMediaAttributeCodes()->willReturn(['picture']);
        $getAttributes->forCode('picture')->willReturn($this->createAttribute('picture'));
        $getAttributes->forCode('size')->willReturn($this->createAttribute('size'));

        $stepExecution->getJobParameters()->willReturn($jobParameters);
        $jobParameters->has('storage')->willReturn(true);
        $jobParameters->get('storage')->willReturn([
            'type' => 'local',
            'file_path' => '/my/path/product.csv',
        ]);
        $jobParameters->get('filters')->willReturn(
            [
                'structure' => ['scope' => 'mobile', 'locales' => ['en_US', 'fr_FR']]
            ]
        );
        $jobParameters->has('with_uuid')->willReturn(false);
        $jobParameters->has('with_media')->willReturn(true);
        $jobParameters->get('with_media')->willReturn(false);

        $channelRepository->findOneByIdentifier('mobile')->willReturn($channel);
        $channel->getLocales()->willReturn(new ArrayCollection([$locale]));
        $channel->getCode()->willReturn('foobar');
        $channel->getLocaleCodes()->willReturn(['en_US', 'de_DE']);

        $normalizer->normalize($product, 'standard', ['with_association_uuids' => false])
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

    function it_processes_a_product_with_media(
        NormalizerInterface $normalizer,
        ChannelRepositoryInterface $channelRepository,
        AttributeRepositoryInterface $attributeRepository,
        GetAttributes $getAttributes,
        StepExecution $stepExecution,
        ChannelInterface $channel,
        LocaleInterface $locale,
        ProductInterface $product,
        JobParameters $jobParameters,
        JobExecution $jobExecution,
        JobInstance $jobInstance,
        WriteValueCollection $valuesCollection,
        ExecutionContext $executionContext,
        AttributeInterface $attribute
    ) {
        $getAttributes->forCode('picture')->willReturn($this->createAttribute('picture'));
        $getAttributes->forCode('pdf_description')->willReturn($this->createAttribute('pdf_description'));

        $stepExecution->getJobParameters()->willReturn($jobParameters);
        $jobParameters->has('storage')->willReturn(true);
        $jobParameters->get('storage')->willReturn([
            'type' => 'local',
            'file_path' => '/my/path/product.csv',
        ]);
        $jobParameters->get('filters')->willReturn(
            [
                'structure' => ['scope' => 'mobile', 'locales' => ['en_US', 'fr_FR']]
            ]
        );
        $jobParameters->has('with_media')->willReturn(true);
        $jobParameters->get('with_media')->willReturn(true);
        $jobParameters->has('with_uuid')->willReturn(true);
        $jobParameters->get('with_uuid')->willReturn(true);

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

        $normalizer->normalize($product, 'standard', ['with_association_uuids' => true])->willReturn($productStandard);

        $this->process($product)->shouldReturn($productStandard);
    }

    public function it_processes_product_with_filter_on_quality_score(
        NormalizerInterface $normalizer,
        ChannelRepositoryInterface $channelRepository,
        AttributeRepositoryInterface $attributeRepository,
        GetAttributes $getAttributes,
        GetNormalizedProductQualityScores $getNormalizedProductQualityScores,
        StepExecution $stepExecution,
        ChannelInterface $channel,
        LocaleInterface $locale,
        ProductInterface $product,
        JobParameters $jobParameters,
        AttributeInterface $attribute
    ) {
        $uuid = Uuid::uuid4();
        $product->getUuid()->willReturn($uuid);

        $getAttributes->forCode('picture')->willReturn($this->createAttribute('picture'));
        $getAttributes->forCode('size')->willReturn($this->createAttribute('size'));

        $attributeRepository->findMediaAttributeCodes()->willReturn(['picture']);

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
        $jobParameters->has('with_uuid')->willReturn(true);
        $jobParameters->get('with_uuid')->willReturn(false);

        $channelRepository->findOneByIdentifier('mobile')->willReturn($channel);
        $channel->getLocales()->willReturn(new ArrayCollection([$locale]));
        $channel->getCode()->willReturn('foobar');
        $channel->getLocaleCodes()->willReturn(['en_US', 'de_DE']);

        $normalizer->normalize($product, 'standard', ['with_association_uuids' => false])->willReturn([
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

        $getNormalizedProductQualityScores->__invoke($uuid,'mobile', ['en_US', 'fr_FR'])
            ->willReturn($normalizedProductWithQualityScores['quality_scores']);

        $this->process($product)->shouldBeLike($normalizedProductWithQualityScores);
    }

    private function createAttribute(string $code): Attribute {
        return new Attribute(
            $code,
            AttributeTypes::NUMBER,
            [],
            true,
            true,
            null,
            null,
            true,
            'decimal',
            []
        );
    }
}
