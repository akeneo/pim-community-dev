<?php

declare(strict_types=1);

namespace spec\Akeneo\AssetManager\Domain\Model\Attribute;

use Akeneo\AssetManager\Domain\Model\Attribute\AttributeCode;
use Akeneo\AssetManager\Domain\Model\Attribute\AttributeIdentifier;
use Akeneo\AssetManager\Domain\Model\Attribute\AttributeIsRequired;
use Akeneo\AssetManager\Domain\Model\Attribute\AttributeOrder;
use Akeneo\AssetManager\Domain\Model\Attribute\AttributeValuePerChannel;
use Akeneo\AssetManager\Domain\Model\Attribute\AttributeValuePerLocale;
use Akeneo\AssetManager\Domain\Model\Attribute\AssetAttribute;
use Akeneo\AssetManager\Domain\Model\LabelCollection;
use Akeneo\AssetManager\Domain\Model\AssetFamily\AssetFamilyIdentifier;
use PhpSpec\ObjectBehavior;

class AssetAttributeSpec extends ObjectBehavior
{
    function let()
    {
        $this->beConstructedThrough('create', [
            AttributeIdentifier::create('designer', 'mentor', 'fingerprint'),
            AssetFamilyIdentifier::fromString('designer'),
            AttributeCode::fromString('mentor'),
            LabelCollection::fromArray(['fr_FR' => 'Mentor', 'en_US' => 'Mentor']),
            AttributeOrder::fromInteger(0),
            AttributeIsRequired::fromBoolean(true),
            AttributeValuePerChannel::fromBoolean(false),
            AttributeValuePerLocale::fromBoolean(false),
            AssetFamilyIdentifier::fromString('designer')
        ]);
    }

    function it_is_initializable()
    {
        $this->shouldHaveType(AssetAttribute::class);
    }

    function it_determines_if_it_has_a_given_order()
    {
        $this->hasOrder(AttributeOrder::fromInteger(0))->shouldReturn(true);
        $this->hasOrder(AttributeOrder::fromInteger(1))->shouldReturn(false);
    }

    function it_normalizes_itself()
    {
        $this->normalize()->shouldReturn([
                'identifier' => 'mentor_designer_fingerprint',
                'asset_family_identifier' => 'designer',
                'code' => 'mentor',
                'labels' => ['fr_FR' => 'Mentor', 'en_US' => 'Mentor'],
                'order' => 0,
                'is_required' => true,
                'value_per_channel' => false,
                'value_per_locale' => false,
                'type' => 'asset',
                'asset_type' => 'designer',
            ]
        );
    }

    function it_updates_its_labels()
    {
        $this->updateLabels(LabelCollection::fromArray(['fr_FR' => 'Tuteur', 'de_DE' => 'Mentor']));
        $this->normalize()->shouldBe([
                'identifier' => 'mentor_designer_fingerprint',
                'asset_family_identifier' => 'designer',
                'code' => 'mentor',
                'labels' => ['fr_FR' => 'Tuteur', 'en_US' => 'Mentor', 'de_DE' => 'Mentor'],
                'order' => 0,
                'is_required' => true,
                'value_per_channel' => false,
                'value_per_locale' => false,
                'type' => 'asset',
                'asset_type' => 'designer',
            ]
        );
    }

    function it_updates_its_asset_type()
    {
        $this->setAssetType(
            AssetFamilyIdentifier::fromString('brand')
        );
        $this->normalize()->shouldBe([
                'identifier' => 'mentor_designer_fingerprint',
                'asset_family_identifier' => 'designer',
                'code' => 'mentor',
                'labels' => ['fr_FR' => 'Mentor', 'en_US' => 'Mentor'],
                'order' => 0,
                'is_required' => true,
                'value_per_channel' => false,
                'value_per_locale' => false,
                'type' => 'asset',
                'asset_type' => 'brand',
            ]
        );
    }

    function it_tells_if_it_has_a_value_per_channel()
    {
        $this->hasValuePerChannel()->shouldReturn(false);
    }

    function it_tells_if_it_has_a_value_per_locale()
    {
        $this->hasValuePerLocale()->shouldReturn(false);
    }
}
