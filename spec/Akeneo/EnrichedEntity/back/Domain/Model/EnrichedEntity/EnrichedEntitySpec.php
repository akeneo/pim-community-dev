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
        $labelCollection = [
            'en_US' => 'Designer',
            'fr_FR' => 'Concepteur'
        ];
        $this->beConstructedThrough('create', [$identifier, $labelCollection]);
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
        $sameEnrichedEntity = EnrichedEntity::create(
            $sameIdentifier,
            []
        );
        $this->equals($sameEnrichedEntity)->shouldReturn(true);

        $anotherIdentifier = EnrichedEntityIdentifier::fromString('same_identifier');
        $sameEnrichedEntity = EnrichedEntity::create(
            $anotherIdentifier,
            []
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

    public function it_updates_labels()
    {
        $labelCollection = LabelCollection::fromArray(['fr_FR' => 'Concepteur']);

        $this->updateLabels($labelCollection);
        $this->getLabel('fr_FR')->shouldBe('Concepteur');
    }
}
