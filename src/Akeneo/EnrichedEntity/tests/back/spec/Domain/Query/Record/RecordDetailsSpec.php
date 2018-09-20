<?php

declare(strict_types=1);

namespace spec\Akeneo\EnrichedEntity\Domain\Query\Record;

use Akeneo\EnrichedEntity\Domain\Model\EnrichedEntity\EnrichedEntityIdentifier;
use Akeneo\EnrichedEntity\Domain\Model\LabelCollection;
use Akeneo\EnrichedEntity\Domain\Model\Record\RecordCode;
use Akeneo\EnrichedEntity\Domain\Model\Record\RecordIdentifier;
use Akeneo\EnrichedEntity\Domain\Query\Record\RecordDetails;
use PhpSpec\ObjectBehavior;

class RecordDetailsSpec extends ObjectBehavior
{
    public function let(
        RecordIdentifier $identifier,
        EnrichedEntityIdentifier $enrichedEntityIdentifier,
        RecordCode $code,
        LabelCollection $labelCollection
    )
    {
        $this->beConstructedWith(
            $identifier,
            $enrichedEntityIdentifier,
            $code,
            $labelCollection,
            []
        );
    }

    public function it_is_initializable()
    {
        $this->shouldHaveType(RecordDetails::class);
    }

    public function it_normalizes_itself(
        RecordIdentifier $identifier,
        EnrichedEntityIdentifier $enrichedEntityIdentifier,
        RecordCode $code,
        LabelCollection $labelCollection
    ) {

        $identifier->normalize()->willReturn('starck_designer_fingerprint');
        $enrichedEntityIdentifier->normalize()->willReturn('designer');
        $code->normalize()->willReturn('starck');
        $labelCollection->normalize()->willReturn(['fr_FR' => 'Philippe Starck']);

        $this->normalize()->shouldReturn([
            'identifier' => 'starck_designer_fingerprint',
            'enriched_entity_identifier' => 'designer',
            'code' => 'starck',
            'labels' => ['fr_FR' => 'Philippe Starck'],
            'values' => [],
        ]);
    }
}
