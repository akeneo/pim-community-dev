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
            AttributeIdentifier::create('designer', 'name'),
            EnrichedEntityIdentifier::fromString('designer'),
            AttributeCode::fromString('name'),
            LabelCollection::fromArray(['fr_FR' => 'Nom', 'en_US' => 'Name']),
            AttributeOrder::fromInteger(0),
            AttributeRequired::fromBoolean(true),
            AttributeValuePerChannel::fromBoolean(true),
            AttributeValuePerLocale::fromBoolean(true),
            AttributeMaxFileSize::fromFloat(300),
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
            AttributeIdentifier::create('designer', 'name'),
            EnrichedEntityIdentifier::fromString('manufacturer'),
            AttributeCode::fromString('name'),
            LabelCollection::fromArray(['fr_FR' => 'Nom', 'en_US' => 'Name']),
            AttributeOrder::fromInteger(0),
            AttributeRequired::fromBoolean(true),
            AttributeValuePerChannel::fromBoolean(true),
            AttributeValuePerLocale::fromBoolean(true),
            AttributeMaxFileSize::fromFloat(300),
            AttributeAllowedExtensions::fromList(['pdf'])
        ]);
    }

    function it_cannot_have_a_code_different_from_the_composite_key()
    {
        $this->shouldThrow(\InvalidArgumentException::class)->during('create', [
            AttributeIdentifier::create('designer', 'name'),
            EnrichedEntityIdentifier::fromString('designer'),
            AttributeCode::fromString('birth_date'),
            LabelCollection::fromArray(['fr_FR' => 'Nom', 'en_US' => 'Name']),
            AttributeOrder::fromInteger(0),
            AttributeRequired::fromBoolean(true),
            AttributeValuePerChannel::fromBoolean(true),
            AttributeValuePerLocale::fromBoolean(true),
            AttributeMaxFileSize::fromFloat(300),
            AttributeAllowedExtensions::fromList(['pdf'])
        ]);
    }
}
