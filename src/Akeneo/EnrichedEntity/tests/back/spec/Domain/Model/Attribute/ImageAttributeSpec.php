<?php

namespace spec\Akeneo\EnrichedEntity\Domain\Model\Attribute;

use Akeneo\EnrichedEntity\Domain\Model\Attribute\AttributeAllowedExtensions;
use Akeneo\EnrichedEntity\Domain\Model\Attribute\AttributeCode;
use Akeneo\EnrichedEntity\Domain\Model\Attribute\AttributeIdentifier;
use Akeneo\EnrichedEntity\Domain\Model\Attribute\AttributeMaxFileSize;
use Akeneo\EnrichedEntity\Domain\Model\Attribute\AttributeOrder;
use Akeneo\EnrichedEntity\Domain\Model\Attribute\AttributeRequired;
use Akeneo\EnrichedEntity\Domain\Model\Attribute\AttributeValuePerChannel;
use Akeneo\EnrichedEntity\Domain\Model\Attribute\AttributeValuePerLocale;
use Akeneo\EnrichedEntity\Domain\Model\Attribute\ImageAttribute;
use Akeneo\EnrichedEntity\Domain\Model\EnrichedEntity\EnrichedEntityIdentifier;
use Akeneo\EnrichedEntity\Domain\Model\LabelCollection;
use PhpSpec\ObjectBehavior;

class ImageAttributeSpec extends ObjectBehavior
{
    function let()
    {
        $this->beConstructedThrough('create', [
            AttributeIdentifier::create('designer', 'image'),
            EnrichedEntityIdentifier::fromString('designer'),
            AttributeCode::fromString('image'),
            LabelCollection::fromArray(['fr_FR' => 'Portrait', 'en_US' => 'Portrait']),
            AttributeOrder::fromInteger(0),
            AttributeRequired::fromBoolean(true),
            AttributeValuePerChannel::fromBoolean(true),
            AttributeValuePerLocale::fromBoolean(true),
            AttributeMaxFileSize::fromString('300'),
            AttributeAllowedExtensions::fromList(['pdf'])
        ]);
    }

    function it_is_initializable()
    {
        $this->shouldHaveType(ImageAttribute::class);
    }

    function it_cannot_have_an_enriched_entity_identifier_different_from_the_composite_key()
    {
        $this->shouldThrow(\InvalidArgumentException::class)->during('create', [
            AttributeIdentifier::create('designer', 'image'),
            EnrichedEntityIdentifier::fromString('manufacturer'),
            AttributeCode::fromString('image'),
            LabelCollection::fromArray(['fr_FR' => 'Portrait', 'en_US' => 'Portrait']),
            AttributeOrder::fromInteger(0),
            AttributeRequired::fromBoolean(true),
            AttributeValuePerChannel::fromBoolean(true),
            AttributeValuePerLocale::fromBoolean(true),
            AttributeMaxFileSize::fromString('300.0'),
            AttributeAllowedExtensions::fromList(['pdf'])
        ]);
    }

    function it_cannot_have_a_code_different_from_the_composite_key()
    {
        $this->shouldThrow(\InvalidArgumentException::class)->during('create', [
            AttributeIdentifier::create('designer', 'image'),
            EnrichedEntityIdentifier::fromString('designer'),
            AttributeCode::fromString('birth_date'),
            LabelCollection::fromArray(['fr_FR' => 'Portrait', 'en_US' => 'Portrait']),
            AttributeOrder::fromInteger(0),
            AttributeRequired::fromBoolean(true),
            AttributeValuePerChannel::fromBoolean(true),
            AttributeValuePerLocale::fromBoolean(true),
            AttributeMaxFileSize::fromString('300'),
            AttributeAllowedExtensions::fromList(['pdf'])
        ]);
    }

    function it_determines_if_it_has_a_given_order()
    {
        $this->hasOrder(AttributeOrder::fromInteger(0))->shouldReturn(true);
        $this->hasOrder(AttributeOrder::fromInteger(1))->shouldReturn(false);
    }

    function it_normalizes_itself()
    {
        $this->normalize()->shouldReturn([
                'identifier'                 => [
                    'enriched_entity_identifier' => 'designer',
                    'identifier'                 => 'image',
                ],
                'enriched_entity_identifier' => 'designer',
                'code'                       => 'image',
                'labels'                     => ['fr_FR' => 'Portrait', 'en_US' => 'Portrait'],
                'order'                      => 0,
                'required'                   => true,
                'value_per_channel'          => true,
                'value_per_locale'           => true,
                'type'                       => 'image',
                'max_file_size'              => '300',
                'allowed_extensions'         => ['pdf'],
            ]
        );
    }

    function it_updates_its_label_and_returns_a_new_instance_of_itself()
    {
        $newPortrait = $this->updateLabels(LabelCollection::fromArray([
            'fr_FR' => 'Portrait',
            'de_DE' => 'Porträt',
        ]));
        $newPortrait->shouldBeAnInstanceOf(ImageAttribute::class);
        $newPortrait->normalize()->shouldBe([
                'identifier'                 => [
                    'enriched_entity_identifier' => 'designer',
                    'identifier'                 => 'image',
                ],
                'enriched_entity_identifier' => 'designer',
                'code'                       => 'image',
                'labels'                     => ['fr_FR' => 'Portrait', 'de_DE' => 'Porträt'],
                'order'                      => 0,
                'required'                   => true,
                'value_per_channel'          => true,
                'value_per_locale'           => true,
                'type'                       => 'image',
                'max_file_size'              => '300',
                'allowed_extensions'         => ['pdf'],
            ]
        );
    }

    function it_updates_its_allowed_extensions_and_returns_a_new_instance_of_itself()
    {
        $newPortrait = $this->setAllowedExtensions(AttributeAllowedExtensions::fromList(['jpeg']));
        $newPortrait->shouldBeAnInstanceOf(ImageAttribute::class);
        $newPortrait->normalize()->shouldBe([
                'identifier'                 => [
                    'enriched_entity_identifier' => 'designer',
                    'identifier'                 => 'image',
                ],
                'enriched_entity_identifier' => 'designer',
                'code'                       => 'image',
                'labels'                     => ['fr_FR' => 'Portrait', 'en_US' => 'Portrait'],
                'order'                      => 0,
                'required'                   => true,
                'value_per_channel'          => true,
                'value_per_locale'           => true,
                'type'                       => 'image',
                'max_file_size'              => '300',
                'allowed_extensions'         => ['jpeg'],
            ]
        );
    }

    function it_updates_its_max_file_size_and_returns_a_new_instance_of_itself()
    {
        $newPortrait = $this->setMaxFileSize(AttributeMaxFileSize::fromString('1000'));
        $newPortrait->shouldBeAnInstanceOf(ImageAttribute::class);
        $newPortrait->normalize()->shouldBe([
                'identifier'                 => [
                    'enriched_entity_identifier' => 'designer',
                    'identifier'                 => 'image',
                ],
                'enriched_entity_identifier' => 'designer',
                'code'                       => 'image',
                'labels'                     => ['fr_FR' => 'Portrait', 'en_US' => 'Portrait'],
                'order'                      => 0,
                'required'                   => true,
                'value_per_channel'          => true,
                'value_per_locale'           => true,
                'type'                       => 'image',
                'max_file_size'              => '1000',
                'allowed_extensions'         => ['pdf'],
            ]
        );
    }

    function it_updates_its_required_property_size_and_returns_a_new_instance_of_itself()
    {
        $newPortrait = $this->setIsRequired(AttributeRequired::fromBoolean(false));
        $newPortrait->shouldBeAnInstanceOf(ImageAttribute::class);
        $newPortrait->normalize()->shouldBe([
                'identifier'                 => [
                    'enriched_entity_identifier' => 'designer',
                    'identifier'                 => 'image',
                ],
                'enriched_entity_identifier' => 'designer',
                'code'                       => 'image',
                'labels'                     => ['fr_FR' => 'Portrait', 'en_US' => 'Portrait'],
                'order'                      => 0,
                'required'                   => false,
                'value_per_channel'          => true,
                'value_per_locale'           => true,
                'type'                       => 'image',
                'max_file_size'              => '300',
                'allowed_extensions'         => ['pdf'],
            ]
        );
    }
}
