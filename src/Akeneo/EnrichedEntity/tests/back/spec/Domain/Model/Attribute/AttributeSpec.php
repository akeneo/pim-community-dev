<?php

namespace spec\Akeneo\EnrichedEntity\Domain\Model\Attribute;

use Akeneo\EnrichedEntity\Domain\Model\Attribute\AbstractAttribute;
use Akeneo\EnrichedEntity\Domain\Model\Attribute\AttributeCode;
use Akeneo\EnrichedEntity\Domain\Model\Attribute\AttributeIdentifier;
use Akeneo\EnrichedEntity\Domain\Model\EnrichedEntity\EnrichedEntityIdentifier;
use PhpSpec\ObjectBehavior;

class AttributeSpec extends ObjectBehavior
{
    public function let()
    {
        $identifier = AttributeIdentifier::create('designer', 'name');
        $enrichedEntityIdentifier = EnrichedEntityIdentifier::fromString('designer');
        $attributeCode = AttributeCode::fromString('name');
        $labelCollection = [
            'en_US' => 'Description',
            'fr_FR' => 'Description'
        ];

        $this->beConstructedThrough('create', [$identifier, $enrichedEntityIdentifier, $attributeCode, $labelCollection]);
    }

    public function it_is_initializable()
    {
        $this->shouldHaveType(AbstractAttribute::class);
    }

    public function it_returns_its_identifier()
    {
        $identifier = AttributeIdentifier::create('designer', 'name');

        $this->getIdentifier()->shouldBeLike($identifier);
    }

    public function it_returns_the_identifier_of_the_enriched_entity_it_belongs_to()
    {
        $enrichedEntityIdentifier = EnrichedEntityIdentifier::fromString('designer');

        $this->getEnrichedEntityIdentifier()->shouldBeLike($enrichedEntityIdentifier);
    }

    public function it_checks_the_identifier_and_code_are_in_sync()
    {
        $this->shouldThrow(\InvalidArgumentException::class)
            ->during('create', [
                AttributeIdentifier::create('designer', 'name'),
                EnrichedEntityIdentifier::fromString('designer'),
                AttributeCode::fromString('wrong_code'),
                []
            ]);
    }

    public function it_checks_the_identifier_and_enriched_entity_identifier_are_in_sync()
    {
        $this->shouldThrow(\InvalidArgumentException::class)
            ->during('create', [
                AttributeIdentifier::create('designer', 'name'),
                EnrichedEntityIdentifier::fromString('wrong_identifier'),
                AttributeCode::fromString('name'),
                []
            ]);
    }

    public function it_is_comparable()
    {
        $sameIdentifier = AttributeIdentifier::create('designer', 'name');
        $sameAttribute = AbstractAttribute::create(
            $sameIdentifier,
            EnrichedEntityIdentifier::fromString('designer'),
            AttributeCode::fromString('name'),
            []
        );
        $this->equals($sameAttribute)->shouldReturn(true);

        $anotherIdentifier = AttributeIdentifier::create('designer', 'type');
        $anotherAttribute = AbstractAttribute::create(
            $anotherIdentifier,
            EnrichedEntityIdentifier::fromString('designer'),
            AttributeCode::fromString('type'),
            []
        );
        $this->equals($anotherAttribute)->shouldReturn(false);
    }
}
