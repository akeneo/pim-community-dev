<?php

namespace Specification\Akeneo\Pim\Enrichment\AssetManager\Component\Connector\Processor\Normalization;

use Akeneo\AssetManager\Domain\Model\Asset\Asset;
use Akeneo\AssetManager\Domain\Model\Asset\AssetCode;
use Akeneo\AssetManager\Domain\Model\Asset\AssetIdentifier;
use Akeneo\AssetManager\Domain\Model\Asset\Value\ChannelReference;
use Akeneo\AssetManager\Domain\Model\Asset\Value\FileData;
use Akeneo\AssetManager\Domain\Model\Asset\Value\LocaleReference;
use Akeneo\AssetManager\Domain\Model\Asset\Value\NumberData;
use Akeneo\AssetManager\Domain\Model\Asset\Value\TextData;
use Akeneo\AssetManager\Domain\Model\Asset\Value\Value;
use Akeneo\AssetManager\Domain\Model\Asset\Value\ValueCollection;
use Akeneo\AssetManager\Domain\Model\AssetFamily\AssetFamilyIdentifier;
use Akeneo\AssetManager\Domain\Model\Attribute\AttributeIdentifier;
use Akeneo\AssetManager\Domain\Model\ChannelIdentifier;
use Akeneo\AssetManager\Domain\Model\LocaleIdentifier;
use Akeneo\Pim\Enrichment\AssetManager\Component\Connector\Processor\BulkMediaFetcher;
use Akeneo\Pim\Enrichment\AssetManager\Component\Connector\Processor\Normalization\AssetProcessor;
use Akeneo\Tool\Component\Batch\Item\DataInvalidItem;
use Akeneo\Tool\Component\Batch\Item\ExecutionContext;
use Akeneo\Tool\Component\Batch\Item\ItemProcessorInterface;
use Akeneo\Tool\Component\Batch\Job\JobInterface;
use Akeneo\Tool\Component\Batch\Job\JobParameters;
use Akeneo\Tool\Component\Batch\Model\JobExecution;
use Akeneo\Tool\Component\Batch\Model\StepExecution;
use PhpSpec\ObjectBehavior;

class AssetProcessorSpec extends ObjectBehavior
{
    function let(
        BulkMediaFetcher $mediaFetcher,
        StepExecution $stepExecution,
        JobParameters $jobParameters,
        JobExecution $jobExecution,
        ExecutionContext $executionContext
    ) {
        $this->beConstructedWith($mediaFetcher);

        $executionContext->get(JobInterface::WORKING_DIRECTORY_PARAMETER)->willReturn('/tmp/akeneo_batch_1234');
        $jobExecution->getExecutionContext()->willReturn($executionContext);
        $stepExecution->getJobExecution()->willReturn($jobExecution);
        $stepExecution->getJobParameters()->willReturn($jobParameters);
        $this->setStepExecution($stepExecution);
    }

    function it_is_a_processor()
    {
        $this->shouldImplement(ItemProcessorInterface::class);
    }

    function it_is_a_normalization_asset_processor()
    {
        $this->shouldHaveType(AssetProcessor::class);
    }

    function it_throws_an_exception_if_item_is_not_an_asset()
    {
        $this->shouldThrow(\InvalidArgumentException::class)->during('process', [new \stdClass()]);
    }

    function it_returns_a_normalized_asset(BulkMediaFetcher $mediaFetcher, JobParameters $jobParameters)
    {
        $asset = Asset::create(
            AssetIdentifier::fromString('asset_packshot_1'),
            AssetFamilyIdentifier::fromString('packshot'),
            AssetCode::fromString('asset_1'),
            ValueCollection::fromValues(
                [
                    Value::create(
                        AttributeIdentifier::fromString('label_123456'),
                        ChannelReference::noReference(),
                        LocaleReference::fromLocaleIdentifier(LocaleIdentifier::fromCode('en_US')),
                        TextData::fromString('My label')
                    ),
                    Value::create(
                        AttributeIdentifier::fromString('label_123456'),
                        ChannelReference::noReference(),
                        LocaleReference::fromLocaleIdentifier(LocaleIdentifier::fromCode('fr_FR')),
                        TextData::fromString('Mon label')
                    ),
                    Value::create(
                        AttributeIdentifier::fromString('filesize_abcdef'),
                        ChannelReference::noReference(),
                        LocaleReference::noReference(),
                        NumberData::fromString('42')
                    ),
                ]
            )
        );

        $jobParameters->get('with_media')->willReturn(false);

        $this->process($asset)->shouldReturn(
            [
                'identifier' => 'asset_packshot_1',
                'code' => 'asset_1',
                'assetFamilyIdentifier' => 'packshot',
                'values' => [
                    'label_123456_en_US' => [
                        'attribute' => 'label_123456',
                        'channel' => null,
                        'locale' => 'en_US',
                        'data' => 'My label',
                    ],
                    'label_123456_fr_FR' => [
                        'attribute' => 'label_123456',
                        'channel' => null,
                        'locale' => 'fr_FR',
                        'data' => 'Mon label',
                    ],
                    'filesize_abcdef' => [
                        'attribute' => 'filesize_abcdef',
                        'channel' => null,
                        'locale' => null,
                        'data' => '42',
                    ],
                ],
            ]
        );
    }

    function it_fetches_the_media_files(BulkMediaFetcher $mediaFetcher, JobParameters $jobParameters)
    {
        $asset = Asset::create(
            AssetIdentifier::fromString('asset_packshot_1'),
            AssetFamilyIdentifier::fromString('packshot'),
            AssetCode::fromString('asset_1'),
            ValueCollection::fromValues(
                [
                    Value::create(
                        AttributeIdentifier::fromString('media_123456'),
                        ChannelReference::noReference(),
                        LocaleReference::noReference(),
                        FileData::createFromNormalize([
                            'filePath' => '1/2/3/jambonabcdef.jpg',
                            'originalFilename' => 'jambon.jpg',
                            'size' => 4096,
                            'mimeType' => 'image/jpg',
                            'extension' => 'jpg',
                            'updatedAt' => '2020-01-01T00:00:00+00:00',
                        ])
                    ),
                    Value::create(
                        AttributeIdentifier::fromString('notice_123456'),
                        ChannelReference::fromChannelIdentifier(ChannelIdentifier::fromCode('ecommerce')),
                        LocaleReference::fromLocaleIdentifier(LocaleIdentifier::fromCode('fr_FR')),
                        FileData::createFromNormalize([
                            'filePath' => 'a/b/c/tartiflette654321.png',
                            'originalFilename' => 'tartiflette.png',
                            'size' => 8192,
                            'mimeType' => 'image/png',
                            'extension' => 'png',
                            'updatedAt' => '2020-01-10T00:00:00+00:00',
                        ])
                    ),
                ]
            )
        );

        $jobParameters->get('with_media')->willReturn(true);
        $mediaFetcher->fetchAll($asset->getValues(), '/tmp/akeneo_batch_1234', 'asset_1')->shouldBeCalled();
        $mediaFetcher->getErrors()->willReturn([]);

        $this->process($asset)->shouldReturn($asset->normalize());
    }

    function it_add_warnings_when_media_fetching_is_in_error(
        BulkMediaFetcher $mediaFetcher,
        StepExecution $stepExecution,
        JobParameters $jobParameters
    ) {
        $asset = Asset::create(
            AssetIdentifier::fromString('asset_packshot_1'),
            AssetFamilyIdentifier::fromString('packshot'),
            AssetCode::fromString('asset_1'),
            ValueCollection::fromValues(
                [
                    Value::create(
                        AttributeIdentifier::fromString('media_123456'),
                        ChannelReference::noReference(),
                        LocaleReference::noReference(),
                        FileData::createFromNormalize(
                            [
                                'filePath' => '1/2/3/jambonabcdef.jpg',
                                'originalFilename' => 'jambon.jpg',
                                'size' => 4096,
                                'mimeType' => 'image/jpg',
                                'extension' => 'jpg',
                                'updatedAt' => '2020-01-01T00:00:00+00:00',
                            ]
                        )
                    ),
                ]
            )
        );

        $jobParameters->get('with_media')->willReturn(true);
        $mediaFetcher->fetchAll($asset->getValues(), '/tmp/akeneo_batch_1234', 'asset_1')->shouldBeCalled();

        $error = [
            'message' => 'The media has not been found or is not currently available',
            'media' => [
                'from' => '1/2/3/jambonabcdef.jpg',
                'to' => [
                    'filePath' => '/tmp/akeneo_batch_1234/files/asset_1/media/',
                    'filename' => 'jambon.jpg',
                ],
                'storage' => 'assetStorage',
            ],
        ];
        $mediaFetcher->getErrors()->willReturn([$error]);
        $stepExecution->addWarning($error['message'], [], new DataInvalidItem($error['media']))->shouldBeCalled();

        $this->process($asset)->shouldReturn($asset->normalize());
    }
}
