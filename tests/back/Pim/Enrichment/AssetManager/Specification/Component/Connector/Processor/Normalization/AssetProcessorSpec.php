<?php

namespace Specification\Akeneo\Pim\Enrichment\AssetManager\Component\Connector\Processor\Normalization;

use Akeneo\AssetManager\Domain\Model\Asset\Asset;
use Akeneo\AssetManager\Domain\Model\Asset\AssetCode;
use Akeneo\AssetManager\Domain\Model\Asset\AssetIdentifier;
use Akeneo\AssetManager\Domain\Model\Asset\Value\ChannelReference;
use Akeneo\AssetManager\Domain\Model\Asset\Value\LocaleReference;
use Akeneo\AssetManager\Domain\Model\Asset\Value\NumberData;
use Akeneo\AssetManager\Domain\Model\Asset\Value\TextData;
use Akeneo\AssetManager\Domain\Model\Asset\Value\Value;
use Akeneo\AssetManager\Domain\Model\Asset\Value\ValueCollection;
use Akeneo\AssetManager\Domain\Model\AssetFamily\AssetFamilyIdentifier;
use Akeneo\AssetManager\Domain\Model\Attribute\AttributeIdentifier;
use Akeneo\AssetManager\Domain\Model\LocaleIdentifier;
use Akeneo\Pim\Enrichment\AssetManager\Component\Connector\Processor\Normalization\AssetProcessor;
use Akeneo\Tool\Component\Batch\Item\ItemProcessorInterface;
use Akeneo\Tool\Component\Batch\Model\StepExecution;
use PhpSpec\ObjectBehavior;

class AssetProcessorSpec extends ObjectBehavior
{
    function let(StepExecution $stepExecution)
    {
        $this->setStepExecution($stepExecution);
    }

    function it_is_a_processor()
    {
        $this->shouldImplement(ItemProcessorInterface::class);
    }

    function it_is_a_normaliztion_asset_processor()
    {
        $this->shouldHaveType(AssetProcessor::class);
    }

    function it_throws_an_exception_if_item_is_not_an_asset()
    {
        $this->shouldThrow(\InvalidArgumentException::class)->during('process', [new \stdClass()]);
    }

    function it_processes_an_asset_item()
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
}
