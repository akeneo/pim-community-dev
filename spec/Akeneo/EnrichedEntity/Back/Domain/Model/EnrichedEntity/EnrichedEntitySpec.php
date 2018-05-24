<?php
declare(strict_types=1);

namespace spec\Akeneo\EnrichedEntity\back\Domain\Model\EnrichedEntity;

use Akeneo\EnrichedEntity\back\Domain\Model\EnrichedEntity\EnrichedEntity;
use Akeneo\EnrichedEntity\back\Domain\Model\EnrichedEntity\EnrichedEntityIdentifier;
use Akeneo\EnrichedEntity\back\Domain\Model\LabelCollection;
use PhpSpec\ObjectBehavior;

class EnrichedEntitySpec extends ObjectBehavior
{
    public function let(EnrichedEntityIdentifier $identifier, LabelCollection $labelCollection)
    {
        $this->beConstructedThrough('define', [$identifier, $labelCollection]);
    }

    public function it_is_initializable()
    {
        $this->shouldHaveType(\Akeneo\EnrichedEntity\back\Domain\Model\EnrichedEntity\EnrichedEntity::class);
    }

    public function it_returns_its_identifier($identifier)
    {
        $this->getIdentifier()->shouldReturn($identifier);
    }

    public function it_is_comparable($identifier)
    {
        $sameIdentifier = EnrichedEntityIdentifier::fromString('same_identifier');
        $sameEnrichedEntity = EnrichedEntity::define(
            $sameIdentifier,
            LabelCollection::fromArray([])
        );
        $identifier->equals($sameIdentifier)->willReturn(true);
        $this->equals($sameEnrichedEntity)->shouldReturn(true);

        $anotherIdentifier = EnrichedEntityIdentifier::fromString('same_identifier');
        $identifier->equals($anotherIdentifier)->willReturn(false);
        $sameEnrichedEntity = \Akeneo\EnrichedEntity\back\Domain\Model\EnrichedEntity\EnrichedEntity::define(
            $anotherIdentifier,
            LabelCollection::fromArray([])
        );
        $this->equals($sameEnrichedEntity)->shouldReturn(false);
    }

    public function it_returns_the_translated_label(
        $labelCollection
    ) {
        $labelCollection->getLabel('en_US')->willReturn('Designer');
        $labelCollection->getLabel('fr_FR')->willReturn('Concepteur');
        $labelCollection->getLabel('ru_RU')->willReturn(null);

        $this->getLabel('fr_FR')->shouldReturn('Concepteur');
        $this->getLabel('en_US')->shouldReturn('Designer');
        $this->getLabel('ru_RU')->shouldReturn(null);
    }

    public function it_returns_the_locale_code_from_which_the_enriched_entity_is_translated($labelCollection) {
        $labelCollection->getLocaleCodes()->willReturn(['en_US', 'fr_FR']);
        $this->getLabelCodes()->shouldReturn(['en_US', 'fr_FR']);
    }
}
