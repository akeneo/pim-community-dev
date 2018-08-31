<?php

namespace spec\Akeneo\EnrichedEntity\Domain\Query\Record;

use Akeneo\EnrichedEntity\Domain\Model\EnrichedEntity\EnrichedEntityIdentifier;
use Akeneo\EnrichedEntity\Domain\Model\LabelCollection;
use Akeneo\EnrichedEntity\Domain\Model\Record\RecordCode;
use Akeneo\EnrichedEntity\Domain\Model\Record\RecordIdentifier;
use Akeneo\EnrichedEntity\Domain\Query\Record\RecordItem;
use PhpSpec\ObjectBehavior;

class RecordItemSpec extends ObjectBehavior
{
    function it_is_initializable()
    {
        $this->shouldHaveType(RecordItem::class);
    }

    function it_normalizes_a_read_model()
    {
        $this->identifier = RecordIdentifier::fromString('designer_starck_fingerprint');
        $this->code = RecordCode::fromString('starck');
        $this->enrichedEntityIdentifier = EnrichedEntityIdentifier::fromString('designer');
        $this->labels = LabelCollection::fromArray([
            'fr_FR' => 'Philippe starck',
            'en_US' => 'Philip starck',
        ]);

        $this->normalize()->shouldReturn(
            [
                'identifier'                 => 'designer_starck_fingerprint',
                'enriched_entity_identifier' => 'designer',
                'code' => 'starck',
                'labels'                     => [
                    'fr_FR' => 'Philippe starck',
                    'en_US' => 'Philip starck',
                ],
            ]
        );
    }
}
