<?php

namespace spec\Akeneo\ReferenceEntity\Infrastructure\Connector\Processor\Normalization;

use Akeneo\ReferenceEntity\Domain\Model\Attribute\AttributeIdentifier;
use Akeneo\ReferenceEntity\Domain\Model\ChannelIdentifier;
use Akeneo\ReferenceEntity\Domain\Model\LocaleIdentifier;
use Akeneo\ReferenceEntity\Domain\Model\Record\Record;
use Akeneo\ReferenceEntity\Domain\Model\Record\RecordCode;
use Akeneo\ReferenceEntity\Domain\Model\Record\RecordIdentifier;
use Akeneo\ReferenceEntity\Domain\Model\Record\Value\ChannelReference;
use Akeneo\ReferenceEntity\Domain\Model\Record\Value\FileData;
use Akeneo\ReferenceEntity\Domain\Model\Record\Value\LocaleReference;
use Akeneo\ReferenceEntity\Domain\Model\Record\Value\NumberData;
use Akeneo\ReferenceEntity\Domain\Model\Record\Value\TextData;
use Akeneo\ReferenceEntity\Domain\Model\Record\Value\Value;
use Akeneo\ReferenceEntity\Domain\Model\Record\Value\ValueCollection;
use Akeneo\ReferenceEntity\Domain\Model\ReferenceEntity\ReferenceEntityIdentifier;
use Akeneo\ReferenceEntity\Infrastructure\Connector\Processor\BulkMediaFetcher;
use Akeneo\ReferenceEntity\Infrastructure\Connector\Processor\Normalization\RecordProcessor;
use Akeneo\Tool\Component\Batch\Item\DataInvalidItem;
use Akeneo\Tool\Component\Batch\Item\ExecutionContext;
use Akeneo\Tool\Component\Batch\Item\ItemProcessorInterface;
use Akeneo\Tool\Component\Batch\Job\JobInterface;
use Akeneo\Tool\Component\Batch\Job\JobParameters;
use Akeneo\Tool\Component\Batch\Model\JobExecution;
use Akeneo\Tool\Component\Batch\Model\StepExecution;
use PhpSpec\ObjectBehavior;

class RecordProcessorSpec extends ObjectBehavior
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

    function it_is_a_normalization_record_processor()
    {
        $this->shouldHaveType(RecordProcessor::class);
    }

    function it_throws_an_exception_if_item_is_not_a_record()
    {
        $this->shouldThrow(\InvalidArgumentException::class)->during('process', [new \stdClass()]);
    }

    function it_returns_a_normalized_record(BulkMediaFetcher $mediaFetcher, JobParameters $jobParameters)
    {
        $record = Record::create(
            RecordIdentifier::fromString('record_brand_1'),
            ReferenceEntityIdentifier::fromString('brand'),
            RecordCode::fromString('record_1'),
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

        $this->process($record)->shouldReturn(
            [
                'identifier' => 'record_brand_1',
                'code' => 'record_1',
                'referenceEntityIdentifier' => 'brand',
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
        $record = Record::create(
            RecordIdentifier::fromString('record_brand_1'),
            ReferenceEntityIdentifier::fromString('brand'),
            RecordCode::fromString('record_1'),
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
        $mediaFetcher->fetchAll($record->getValues(), '/tmp/akeneo_batch_1234', 'record_1')->shouldBeCalled();
        $mediaFetcher->getErrors()->willReturn([]);

        $this->process($record)->shouldReturn($record->normalize());
    }

    function it_add_warnings_when_media_fetching_is_in_error(
        BulkMediaFetcher $mediaFetcher,
        StepExecution $stepExecution,
        JobParameters $jobParameters
    ) {
        $record = Record::create(
            RecordIdentifier::fromString('record_brand_1'),
            ReferenceEntityIdentifier::fromString('brand'),
            RecordCode::fromString('record_1'),
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
        $mediaFetcher->fetchAll($record->getValues(), '/tmp/akeneo_batch_1234', 'record_1')->shouldBeCalled();

        $error = [
            'message' => 'The media has not been found or is not currently available',
            'media' => [
                'from' => '1/2/3/jambonabcdef.jpg',
                'to' => [
                    'filePath' => '/tmp/akeneo_batch_1234/files/record_1/media/',
                    'filename' => 'jambon.jpg',
                ],
                'storage' => 'recordStorage',
            ],
        ];
        $mediaFetcher->getErrors()->willReturn([$error]);
        $stepExecution->addWarning($error['message'], [], new DataInvalidItem($error['media']))->shouldBeCalled();

        $this->process($record)->shouldReturn($record->normalize());
    }
}
