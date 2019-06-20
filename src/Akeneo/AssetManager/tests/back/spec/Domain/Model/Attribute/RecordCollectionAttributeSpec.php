<?php

declare(strict_types=1);

namespace spec\Akeneo\ReferenceEntity\Domain\Model\Attribute;

use Akeneo\ReferenceEntity\Domain\Model\Attribute\AttributeCode;
use Akeneo\ReferenceEntity\Domain\Model\Attribute\AttributeIdentifier;
use Akeneo\ReferenceEntity\Domain\Model\Attribute\AttributeIsRequired;
use Akeneo\ReferenceEntity\Domain\Model\Attribute\AttributeOrder;
use Akeneo\ReferenceEntity\Domain\Model\Attribute\AttributeValuePerChannel;
use Akeneo\ReferenceEntity\Domain\Model\Attribute\AttributeValuePerLocale;
use Akeneo\ReferenceEntity\Domain\Model\Attribute\RecordCollectionAttribute;
use Akeneo\ReferenceEntity\Domain\Model\LabelCollection;
use Akeneo\ReferenceEntity\Domain\Model\ReferenceEntity\ReferenceEntityIdentifier;
use PhpSpec\ObjectBehavior;

class RecordCollectionAttributeSpec extends ObjectBehavior
{
    function let()
    {
        $this->beConstructedThrough('create', [
            AttributeIdentifier::create('designer', 'brands', 'fingerprint'),
            ReferenceEntityIdentifier::fromString('designer'),
            AttributeCode::fromString('brands'),
            LabelCollection::fromArray(['fr_FR' => 'Marques', 'en_US' => 'Brands']),
            AttributeOrder::fromInteger(0),
            AttributeIsRequired::fromBoolean(true),
            AttributeValuePerChannel::fromBoolean(false),
            AttributeValuePerLocale::fromBoolean(false),
            ReferenceEntityIdentifier::fromString('brand')
        ]);
    }

    function it_is_initializable()
    {
        $this->shouldHaveType(RecordCollectionAttribute::class);
    }

    function it_determines_if_it_has_a_given_order()
    {
        $this->hasOrder(AttributeOrder::fromInteger(0))->shouldReturn(true);
        $this->hasOrder(AttributeOrder::fromInteger(1))->shouldReturn(false);
    }

    function it_normalizes_itself()
    {
        $this->normalize()->shouldReturn([
                'identifier' => 'brands_designer_fingerprint',
                'reference_entity_identifier' => 'designer',
                'code' => 'brands',
                'labels' => ['fr_FR' => 'Marques', 'en_US' => 'Brands'],
                'order' => 0,
                'is_required' => true,
                'value_per_channel' => false,
                'value_per_locale' => false,
                'type' => 'record_collection',
                'record_type' => 'brand',
            ]
        );
    }

    function it_updates_its_labels()
    {
        $this->updateLabels(LabelCollection::fromArray(['fr_FR' => 'Anciennes Marques', 'en_US' => 'Old Brands']));
        $this->normalize()->shouldBe([
                'identifier' => 'brands_designer_fingerprint',
                'reference_entity_identifier' => 'designer',
                'code' => 'brands',
                'labels' => ['fr_FR' => 'Anciennes Marques', 'en_US' => 'Old Brands'],
                'order' => 0,
                'is_required' => true,
                'value_per_channel' => false,
                'value_per_locale' => false,
                'type' => 'record_collection',
                'record_type' => 'brand',
            ]
        );
    }

    function it_updates_its_record_type()
    {
        $this->setRecordType(
            ReferenceEntityIdentifier::fromString('color')
        );

        $this->normalize()->shouldBe([
                'identifier' => 'brands_designer_fingerprint',
                'reference_entity_identifier' => 'designer',
                'code' => 'brands',
                'labels' => ['fr_FR' => 'Marques', 'en_US' => 'Brands'],
                'order' => 0,
                'is_required' => true,
                'value_per_channel' => false,
                'value_per_locale' => false,
                'type' => 'record_collection',
                'record_type' => 'color',
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
