<?php

declare(strict_types=1);

namespace spec\Akeneo\ReferenceEntity\Domain\Model\Attribute;

use Akeneo\ReferenceEntity\Domain\Model\Attribute\AttributeCode;
use Akeneo\ReferenceEntity\Domain\Model\Attribute\AttributeIdentifier;
use Akeneo\ReferenceEntity\Domain\Model\Attribute\AttributeIsRequired;
use Akeneo\ReferenceEntity\Domain\Model\Attribute\AttributeOrder;
use Akeneo\ReferenceEntity\Domain\Model\Attribute\AttributeValuePerChannel;
use Akeneo\ReferenceEntity\Domain\Model\Attribute\AttributeValuePerLocale;
use Akeneo\ReferenceEntity\Domain\Model\Attribute\RecordAttribute;
use Akeneo\ReferenceEntity\Domain\Model\LabelCollection;
use Akeneo\ReferenceEntity\Domain\Model\ReferenceEntity\ReferenceEntityIdentifier;
use PhpSpec\ObjectBehavior;

class RecordAttributeSpec extends ObjectBehavior
{
    function let()
    {
        $this->beConstructedThrough('create', [
            AttributeIdentifier::create('designer', 'mentor', 'fingerprint'),
            ReferenceEntityIdentifier::fromString('designer'),
            AttributeCode::fromString('mentor'),
            LabelCollection::fromArray(['fr_FR' => 'Mentor', 'en_US' => 'Mentor']),
            AttributeOrder::fromInteger(0),
            AttributeIsRequired::fromBoolean(true),
            AttributeValuePerChannel::fromBoolean(false),
            AttributeValuePerLocale::fromBoolean(false),
            ReferenceEntityIdentifier::fromString('designer')
        ]);
    }

    function it_is_initializable()
    {
        $this->shouldHaveType(RecordAttribute::class);
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
                'reference_entity_identifier' => 'designer',
                'code' => 'mentor',
                'labels' => ['fr_FR' => 'Mentor', 'en_US' => 'Mentor'],
                'order' => 0,
                'is_required' => true,
                'value_per_channel' => false,
                'value_per_locale' => false,
                'type' => 'record',
                'record_type' => 'designer',
            ]
        );
    }

    function it_updates_its_labels()
    {
        $this->updateLabels(LabelCollection::fromArray(['fr_FR' => 'Tuteur', 'de_DE' => 'Mentor']));
        $this->normalize()->shouldBe([
                'identifier' => 'mentor_designer_fingerprint',
                'reference_entity_identifier' => 'designer',
                'code' => 'mentor',
                'labels' => ['fr_FR' => 'Tuteur', 'en_US' => 'Mentor', 'de_DE' => 'Mentor'],
                'order' => 0,
                'is_required' => true,
                'value_per_channel' => false,
                'value_per_locale' => false,
                'type' => 'record',
                'record_type' => 'designer',
            ]
        );
    }

    function it_updates_its_record_type()
    {
        $this->setRecordType(
            ReferenceEntityIdentifier::fromString('brand')
        );
        $this->normalize()->shouldBe([
                'identifier' => 'mentor_designer_fingerprint',
                'reference_entity_identifier' => 'designer',
                'code' => 'mentor',
                'labels' => ['fr_FR' => 'Mentor', 'en_US' => 'Mentor'],
                'order' => 0,
                'is_required' => true,
                'value_per_channel' => false,
                'value_per_locale' => false,
                'type' => 'record',
                'record_type' => 'brand',
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
