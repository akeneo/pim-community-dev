<?php
declare(strict_types=1);

namespace spec\Akeneo\EnrichedEntity\back\Domain\Model\Record;

use Akeneo\EnrichedEntity\back\Domain\Model\EnrichedEntity\EnrichedEntityIdentifier;
use Akeneo\EnrichedEntity\back\Domain\Model\LabelCollection;
use Akeneo\EnrichedEntity\back\Domain\Model\Record\Record;
use Akeneo\EnrichedEntity\back\Domain\Model\Record\RecordIdentifier;
use PhpSpec\ObjectBehavior;

class RecordSpec extends ObjectBehavior
{
    public function let()
    {
        $identifier = RecordIdentifier::fromString('stark');
        $enrichedEntityIdentifier = EnrichedEntityIdentifier::fromString('designer');
        $labelCollection = LabelCollection::fromArray([
            'en_US' => 'Stark',
            'fr_FR' => 'Stark'
        ]);

        $this->beConstructedThrough('create', [$identifier, $enrichedEntityIdentifier, $labelCollection]);
    }

    public function it_is_initializable()
    {
        $this->shouldHaveType(Record::class);
    }

    public function it_returns_its_identifier()
    {
        $identifier = RecordIdentifier::fromString('stark');

        $this->getIdentifier()->shouldBeLike($identifier);
    }

    public function it_returns_the_identifier_of_the_enriched_entity_it_belongs_to()
    {
        $enrichedEntityIdentifier = EnrichedEntityIdentifier::fromString('designer');

        $this->getEnrichedEntityIdentifier()->shouldBeLike($enrichedEntityIdentifier);
    }

    public function it_is_comparable()
    {
        $sameIdentifier = RecordIdentifier::fromString('stark');
        $sameEnrichedEntityIdentifier = EnrichedEntityIdentifier::fromString('designer');
        $sameRecord = Record::create(
            $sameIdentifier,
            $sameEnrichedEntityIdentifier,
            LabelCollection::fromArray([])
        );

        $this->equals($sameRecord)->shouldReturn(true);

        $anotherIdentifier = RecordIdentifier::fromString('jony_ive');
        $anotherRecord = Record::create(
            $anotherIdentifier,
            $sameEnrichedEntityIdentifier,
            LabelCollection::fromArray([])
        );
        $this->equals($anotherRecord)->shouldReturn(false);

        $anotherEnrichedEntityIdentifier = EnrichedEntityIdentifier::fromString('sofa');
        $anotherRecord = Record::create(
            $sameIdentifier,
            $anotherEnrichedEntityIdentifier,
            LabelCollection::fromArray([])
        );
        $this->equals($anotherRecord)->shouldReturn(false);
    }
}
