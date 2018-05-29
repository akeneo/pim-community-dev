<?php
declare(strict_types=1);

namespace spec\Akeneo\EnrichedEntity\back\Domain\Model\EnrichedEntity;

use Akeneo\EnrichedEntity\back\Domain\Model\EnrichedEntity\EnrichedEntity;
use Akeneo\EnrichedEntity\back\Domain\Model\EnrichedEntity\EnrichedEntityIdentifier;
use Akeneo\EnrichedEntity\back\Domain\Model\LabelCollection;
use PhpSpec\ObjectBehavior;

class EnrichedEntitySpec extends ObjectBehavior
{
    public function let()
    {
        $identifier = EnrichedEntityIdentifier::fromString('designer');
        $labelCollection = LabelCollection::fromArray([
            'en_US' => 'Designer',
            'fr_FR' => 'Concepteur'
        ]);

        $this->beConstructedThrough('define', [$identifier, $labelCollection]);
    }

    public function it_is_initializable()
    {
        $this->shouldHaveType(EnrichedEntity::class);
    }

    public function it_returns_its_identifier()
    {
        $identifier = EnrichedEntityIdentifier::fromString('designer');
        $this->getIdentifier()->shouldBeLike($identifier);
    }

    public function it_is_comparable_to_another_enriched_entity()
    {
        $sameIdentifier = EnrichedEntityIdentifier::fromString('designer');
        $sameEnrichedEntity = EnrichedEntity::define(
            $sameIdentifier,
            LabelCollection::fromArray([])
        );
        $this->equals($sameEnrichedEntity)->shouldReturn(true);

        $anotherIdentifier = EnrichedEntityIdentifier::fromString('same_identifier');
        $sameEnrichedEntity = EnrichedEntity::define(
            $anotherIdentifier,
            LabelCollection::fromArray([])
        );
        $this->equals($sameEnrichedEntity)->shouldReturn(false);
    }

    public function it_returns_the_translated_label() {
        $this->getLabel('fr_FR')->shouldReturn('Concepteur');
        $this->getLabel('en_US')->shouldReturn('Designer');
        $this->getLabel('ru_RU')->shouldReturn(null);
    }

    public function it_returns_the_locale_code_from_which_the_enriched_entity_is_translated($labelCollection) {
        $this->getLabelCodes()->shouldReturn(['en_US', 'fr_FR']);
    }
}
