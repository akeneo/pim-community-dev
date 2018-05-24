<?php
declare(strict_types=1);

namespace spec\Akeneo\EnrichedEntity\back\Domain\Model\Record;

use Akeneo\EnrichedEntity\back\Domain\Model\EnrichedEntity\EnrichedEntityIdentifier;
use Akeneo\EnrichedEntity\back\Domain\Model\LabelCollection;
use Akeneo\EnrichedEntity\back\Domain\Model\Record\RecordIdentifier;
use PhpSpec\ObjectBehavior;

class RecordSpec extends ObjectBehavior
{
    public function let(
        RecordIdentifier $identifier,
        LabelCollection $labelCollection,
        \Akeneo\EnrichedEntity\back\Domain\Model\EnrichedEntity\EnrichedEntityIdentifier $enrichedEntityIdentifier
    )
    {
        $this->beConstructedThrough('create', [$identifier, $enrichedEntityIdentifier, $labelCollection]);
    }

    public function it_is_initializable()
    {
        $this->shouldImplement(\Akeneo\EnrichedEntity\back\Domain\Model\Record\Record::class);
    }

    public function it_returns_its_identifier($identifier)
    {
        $this->getIdentifier()->shouldReturn($identifier);
    }

    public function it_returns_the_identifier_of_the_enriched_entity_it_belongs_to($enrichedEntityIdentifier)
    {
        $this->getEnrichedEntityIdentifier()->shouldReturn($enrichedEntityIdentifier);
    }

    public function it_is_comparable($identifier, $enrichedEntityIdentifier)
    {
        $sameIdentifier = RecordIdentifier::fromString('same_identifier');
        $identifier->equals($sameIdentifier)->willReturn(true);
        $sameEnrichedEntityIdentifier = EnrichedEntityIdentifier::fromString('an_enriched_entity');
        $enrichedEntityIdentifier->equals($sameEnrichedEntityIdentifier)->willReturn(true);
        $sameRecord = \Akeneo\EnrichedEntity\back\Domain\Model\Record\Record::create(
            $sameIdentifier,
            $sameEnrichedEntityIdentifier,
            \Akeneo\EnrichedEntity\back\Domain\Model\LabelCollection::fromArray([])
        );
        $this->equals($sameRecord)->shouldReturn(true);

        $anotherIdentifier = RecordIdentifier::fromString('another_identifier');
        $identifier->equals($anotherIdentifier)->willReturn(false);
        $anotherRecord = \Akeneo\EnrichedEntity\back\Domain\Model\Record\Record::create(
            $anotherIdentifier,
            $sameEnrichedEntityIdentifier,
            \Akeneo\EnrichedEntity\back\Domain\Model\LabelCollection::fromArray([])
        );
        $this->equals($anotherRecord)->shouldReturn(false);
    }
}
