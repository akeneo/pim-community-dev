<?php

namespace spec\Akeneo\EnrichedEntity\back\Domain\Query;

use Akeneo\EnrichedEntity\back\Domain\Model\EnrichedEntity\EnrichedEntityIdentifier;
use Akeneo\EnrichedEntity\back\Domain\Model\LabelCollection;
use Akeneo\EnrichedEntity\back\Domain\Query\EnrichedEntityItem;
use PhpSpec\ObjectBehavior;

class EnrichedEntityItemSpec extends ObjectBehavior
{
    function it_is_initializable()
    {
        $this->shouldHaveType(EnrichedEntityItem::class);
    }

    function it_normalizes_a_read_model()
    {
        $this->identifier = EnrichedEntityIdentifier::fromString('starck');
        $this->labels = LabelCollection::fromArray([
            'fr_FR' => 'Philippe starck',
            'en_US' => 'Philip starck',
        ]);

        $this->normalize()->shouldReturn(
            [
                'identifier'                 => 'starck',
                'labels'                     => [
                    'fr_FR' => 'Philippe starck',
                    'en_US' => 'Philip starck',
                ],
            ]
        );
    }
}
