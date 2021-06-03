<?php

declare(strict_types=1);

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2018 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace spec\Akeneo\AssetManager\Domain\Model\Asset;

use Akeneo\AssetManager\Domain\Event\AssetCreatedEvent;
use Akeneo\AssetManager\Domain\Model\Attribute\AttributeIdentifier;
use Akeneo\AssetManager\Domain\Model\ChannelIdentifier;
use Akeneo\AssetManager\Domain\Model\LocaleIdentifier;
use Akeneo\AssetManager\Domain\Model\Asset\Asset;
use Akeneo\AssetManager\Domain\Model\Asset\AssetCode;
use Akeneo\AssetManager\Domain\Model\Asset\AssetIdentifier;
use Akeneo\AssetManager\Domain\Model\Asset\Value\ChannelReference;
use Akeneo\AssetManager\Domain\Model\Asset\Value\LocaleReference;
use Akeneo\AssetManager\Domain\Model\Asset\Value\TextData;
use Akeneo\AssetManager\Domain\Model\Asset\Value\Value;
use Akeneo\AssetManager\Domain\Model\Asset\Value\ValueCollection;
use Akeneo\AssetManager\Domain\Model\AssetFamily\AssetFamilyIdentifier;
use Akeneo\AssetManager\Domain\Query\Attribute\ValueKey;
use Akeneo\Pim\Enrichment\AssetManager\Component\Value\AssetCollectionValue;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;

class AssetSpec extends ObjectBehavior
{
    function let()
    {
        $identifier = AssetIdentifier::fromString('designer_starck_fingerprint');
        $assetFamilyIdentifier = AssetFamilyIdentifier::fromString('designer');
        $assetCode = AssetCode::fromString('starck');
        $valueCollection = ValueCollection::fromValues([
            Value::create(
                AttributeIdentifier::create('designer', 'label', 'fingerprint'),
                ChannelReference::noReference(),
                LocaleReference::fromLocaleIdentifier(LocaleIdentifier::fromCode('fr_FR')),
                TextData::fromString('Stark')
            ),
            Value::create(
                AttributeIdentifier::create('designer', 'label', 'fingerprint'),
                ChannelReference::noReference(),
                LocaleReference::fromLocaleIdentifier(LocaleIdentifier::fromCode('en_US')),
                TextData::fromString('Stark')
            ),
            Value::create(
                AttributeIdentifier::create('designer', 'description', 'fingerprint'),
                ChannelReference::fromChannelIdentifier(ChannelIdentifier::fromCode('ecommerce')),
                LocaleReference::fromLocaleIdentifier(LocaleIdentifier::fromCode('fr_FR')),
                TextData::fromString('.one value per channel ecommerce / one value per locale fr_FR.')
            ),
        ]);

        $this->beConstructedThrough('create', [
            $identifier,
            $assetFamilyIdentifier,
            $assetCode,
            $valueCollection
        ]);
    }

    function it_is_initializable()
    {
        $this->shouldHaveType(Asset::class);
    }

    function it_returns_its_identifier()
    {
        $identifier = AssetIdentifier::fromString('designer_starck_fingerprint');

        $this->getIdentifier()->shouldBeLike($identifier);
    }

    function it_returns_the_identifier_of_the_asset_family_it_belongs_to()
    {
        $assetFamilyIdentifier = AssetFamilyIdentifier::fromString('designer');

        $this->getAssetFamilyIdentifier()->shouldBeLike($assetFamilyIdentifier);
    }

    function it_is_comparable()
    {
        $sameIdentifier = AssetIdentifier::fromString('designer_starck_fingerprint');
        $sameAsset = Asset::create(
            $sameIdentifier,
            AssetFamilyIdentifier::fromString('designer'),
            AssetCode::fromString('starck'),
            ValueCollection::fromValues([])
        );
        $this->equals($sameAsset)->shouldReturn(true);

        $anotherIdentifier = AssetIdentifier::fromString('designer_jony_ive_other-fingerprint');
        $anotherAsset = Asset::create(
            $anotherIdentifier,
            AssetFamilyIdentifier::fromString('designer'),
            AssetCode::fromString('jony_ive'),
            ValueCollection::fromValues([])
        );
        $this->equals($anotherAsset)->shouldReturn(false);
    }

    function it_sets_a_value_to_the_value_collection()
    {
        $valueKey = ValueKey::create(
            AttributeIdentifier::fromString('name'),
            ChannelReference::noReference(),
            LocaleReference::noReference()
        );
        $value = Value::create(
            AttributeIdentifier::fromString('name'),
            ChannelReference::noReference(),
            LocaleReference::noReference(),
            TextData::fromString('Philippe Stark')
        );

        $this->findValue($valueKey)->shouldBeNull();

        $this->setValue($value);

        $this->findValue($valueKey)->shouldBeEqualTo($value);
    }

    function it_add_created_event_on_recorded_events_when_created()
    {
        $identifier = AssetIdentifier::fromString('designer_starck_fingerprint');
        $assetFamilyIdentifier = AssetFamilyIdentifier::fromString('designer');
        $assetCode = AssetCode::fromString('starck');
        $this->beConstructedThrough('create', [
            $identifier,
            $assetFamilyIdentifier,
            $assetCode,
            ValueCollection::fromValues([])
        ]);

        $recordedEvents = $this->getRecordedEvents();

        $recordedEvents->shouldHaveCount(1);
        $recordedEvents[0]->shouldBeAnInstanceOf(AssetCreatedEvent::class);
        $recordedEvents[0]->getAssetIdentifier()->shouldReturn($identifier);
        $recordedEvents[0]->getAssetCode()->shouldReturn($assetCode);
        $recordedEvents[0]->getAssetFamilyIdentifier()->shouldReturn($assetFamilyIdentifier);
    }

    function it_clear_recorded_events()
    {
        $this->getRecordedEvents()->shouldHaveCount(1);
        $this->clearRecordedEvents();
        $this->getRecordedEvents()->shouldHaveCount(0);
    }

    function it_does_not_add_created_event_on_recorded_events_when_loaded()
    {
        $identifier = AssetIdentifier::fromString('designer_starck_fingerprint');
        $assetFamilyIdentifier = AssetFamilyIdentifier::fromString('designer');
        $assetCode = AssetCode::fromString('starck');
        $valueCollection = ValueCollection::fromValues([
            Value::create(
                AttributeIdentifier::create('designer', 'label', 'fingerprint'),
                ChannelReference::noReference(),
                LocaleReference::fromLocaleIdentifier(LocaleIdentifier::fromCode('fr_FR')),
                TextData::fromString('Stark')
            ),
            Value::create(
                AttributeIdentifier::create('designer', 'label', 'fingerprint'),
                ChannelReference::noReference(),
                LocaleReference::fromLocaleIdentifier(LocaleIdentifier::fromCode('en_US')),
                TextData::fromString('Stark')
            ),
            Value::create(
                AttributeIdentifier::create('designer', 'description', 'fingerprint'),
                ChannelReference::fromChannelIdentifier(ChannelIdentifier::fromCode('ecommerce')),
                LocaleReference::fromLocaleIdentifier(LocaleIdentifier::fromCode('fr_FR')),
                TextData::fromString('.one value per channel ecommerce / one value per locale fr_FR.')
            ),
        ]);

        $this->beConstructedThrough('fromState', [
            $identifier,
            $assetFamilyIdentifier,
            $assetCode,
            $valueCollection,
            new \DateTimeImmutable(),
            new \DateTimeImmutable(),
        ]);

        $this->getRecordedEvents()->shouldReturn([]);
    }

    function it_does_not_update_when_value_do_not_change()
    {
        $identifier = AssetIdentifier::fromString('designer_starck_fingerprint');
        $assetFamilyIdentifier = AssetFamilyIdentifier::fromString('designer');
        $assetCode = AssetCode::fromString('starck');
        $valueCollection = ValueCollection::fromValues([
            Value::create(
                AttributeIdentifier::create('designer', 'label', 'fingerprint'),
                ChannelReference::noReference(),
                LocaleReference::fromLocaleIdentifier(LocaleIdentifier::fromCode('fr_FR')),
                TextData::fromString('Stark')
            ),
            Value::create(
                AttributeIdentifier::create('designer', 'label', 'fingerprint'),
                ChannelReference::noReference(),
                LocaleReference::fromLocaleIdentifier(LocaleIdentifier::fromCode('en_US')),
                TextData::fromString('Stark')
            ),
            Value::create(
                AttributeIdentifier::create('designer', 'description', 'fingerprint'),
                ChannelReference::fromChannelIdentifier(ChannelIdentifier::fromCode('ecommerce')),
                LocaleReference::fromLocaleIdentifier(LocaleIdentifier::fromCode('fr_FR')),
                TextData::fromString('.one value per channel ecommerce / one value per locale fr_FR.')
            ),
        ]);

        $dateUpdated = new \DateTimeImmutable();
        $this->beConstructedThrough('fromState', [
            $identifier,
            $assetFamilyIdentifier,
            $assetCode,
            $valueCollection,
            new \DateTimeImmutable(),
            $dateUpdated,
        ]);

        $this->setValue(Value::create(
            AttributeIdentifier::create('designer', 'description', 'fingerprint'),
            ChannelReference::fromChannelIdentifier(ChannelIdentifier::fromCode('ecommerce')),
            LocaleReference::fromLocaleIdentifier(LocaleIdentifier::fromCode('fr_FR')),
            TextData::fromString('.one value per channel ecommerce / one value per locale fr_FR.')
        ));

        $this->getUpdatedAt()->shouldReturn($dateUpdated);
        $this->getRecordedEvents()->shouldReturn([]);
    }



    function it_normalizes_itself()
    {
        $this->normalize()->shouldReturn([
            'identifier' => 'designer_starck_fingerprint',
            'code' => 'starck',
            'assetFamilyIdentifier' => 'designer',
            'values' => [
                'label_designer_fingerprint_fr_FR' => [
                    'attribute' => 'label_designer_fingerprint',
                    'channel'   => null,
                    'locale'    => 'fr_FR',
                    'data'      => 'Stark',
                ],
                'label_designer_fingerprint_en_US' => [
                    'attribute' => 'label_designer_fingerprint',
                    'channel'   => null,
                    'locale'    => 'en_US',
                    'data'      => 'Stark',
                ],
                'description_designer_fingerprint_ecommerce_fr_FR' => [
                    'attribute' => 'description_designer_fingerprint',
                    'channel'   => 'ecommerce',
                    'locale'    => 'fr_FR',
                    'data'      => '.one value per channel ecommerce / one value per locale fr_FR.',
                ],
            ],
        ]);
    }

    function it_filters_values()
    {
        $this->filterValues(fn (Value $value) => false)->normalize()->shouldReturn([]);
        $this->filterValues(fn (Value $value) => true)
            ->findValue(ValueKey::createFromNormalized('description_designer_fingerprint_ecommerce_fr_FR'))
            ->shouldNotBeNull();
    }
}
