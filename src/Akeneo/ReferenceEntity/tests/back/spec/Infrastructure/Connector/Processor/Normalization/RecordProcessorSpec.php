<?php

namespace spec\Akeneo\ReferenceEntity\Infrastructure\Connector\Processor\Normalization;

use Akeneo\ReferenceEntity\Domain\Model\Attribute\AttributeIdentifier;
use Akeneo\ReferenceEntity\Domain\Model\LocaleIdentifier;
use Akeneo\ReferenceEntity\Domain\Model\Record\Record;
use Akeneo\ReferenceEntity\Domain\Model\Record\RecordCode;
use Akeneo\ReferenceEntity\Domain\Model\Record\RecordIdentifier;
use Akeneo\ReferenceEntity\Domain\Model\Record\Value\ChannelReference;
use Akeneo\ReferenceEntity\Domain\Model\Record\Value\LocaleReference;
use Akeneo\ReferenceEntity\Domain\Model\Record\Value\NumberData;
use Akeneo\ReferenceEntity\Domain\Model\Record\Value\TextData;
use Akeneo\ReferenceEntity\Domain\Model\Record\Value\Value;
use Akeneo\ReferenceEntity\Domain\Model\Record\Value\ValueCollection;
use Akeneo\ReferenceEntity\Domain\Model\ReferenceEntity\ReferenceEntityIdentifier;
use Akeneo\ReferenceEntity\Infrastructure\Connector\Processor\Normalization\RecordProcessor;
use Akeneo\Tool\Component\Batch\Item\ItemProcessorInterface;
use PhpSpec\ObjectBehavior;

class RecordProcessorSpec extends ObjectBehavior
{
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

    function it_returns_a_normalized_record()
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
}
