<?php
declare(strict_types=1);

namespace spec\Akeneo\EnrichedEntity\Domain\Model\Record;

use Akeneo\EnrichedEntity\Domain\Model\EnrichedEntity\EnrichedEntityIdentifier;
use Akeneo\EnrichedEntity\Domain\Model\Record\Record;
use Akeneo\EnrichedEntity\Domain\Model\Record\RecordCode;
use Akeneo\EnrichedEntity\Domain\Model\Record\RecordIdentifier;
use PhpSpec\ObjectBehavior;

class RecordSpec extends ObjectBehavior
{
    public function let()
    {
        $identifier = RecordIdentifier::fromString('designer', 'starck');
        $enrichedEntityIdentifier = EnrichedEntityIdentifier::fromString('designer');
        $recordCode = RecordCode::fromString('starck');
        $labelCollection = [
            'en_US' => 'Stark',
            'fr_FR' => 'Stark'
        ];

        $this->beConstructedThrough('create', [$identifier, $enrichedEntityIdentifier, $recordCode, $labelCollection]);
    }

    public function it_is_initializable()
    {
        $this->shouldHaveType(Record::class);
    }

    public function it_returns_its_identifier()
    {
        $identifier = RecordIdentifier::fromString('designer', 'starck');

        $this->getIdentifier()->shouldBeLike($identifier);
    }

    public function it_returns_the_identifier_of_the_enriched_entity_it_belongs_to()
    {
        $enrichedEntityIdentifier = EnrichedEntityIdentifier::fromString('designer');

        $this->getEnrichedEntityIdentifier()->shouldBeLike($enrichedEntityIdentifier);
    }

    public function it_is_comparable()
    {
        $sameIdentifier = RecordIdentifier::fromString('designer', 'starck');
        $sameEnrichedEntityIdentifier = EnrichedEntityIdentifier::fromString('designer');
        $sameRecordCode = RecordCode::fromString('starck');
        $sameRecord = Record::create($sameIdentifier, $sameEnrichedEntityIdentifier, $sameRecordCode, []);

        $this->equals($sameRecord)->shouldReturn(true);

        $anotherIdentifier = RecordIdentifier::fromString('designer', 'jony_ive');
        $anotherRecord = Record::create($anotherIdentifier, $sameEnrichedEntityIdentifier, $sameRecordCode, []);
        $this->equals($anotherRecord)->shouldReturn(false);

        $anotherEnrichedEntityIdentifier = EnrichedEntityIdentifier::fromString('sofa');
        $anotherRecord = Record::create($sameIdentifier, $anotherEnrichedEntityIdentifier, $sameRecordCode, []);
        $this->equals($anotherRecord)->shouldReturn(false);
    }
}
