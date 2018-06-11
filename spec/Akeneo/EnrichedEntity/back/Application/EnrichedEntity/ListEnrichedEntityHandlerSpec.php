<?php

namespace spec\Akeneo\EnrichedEntity\back\Application\EnrichedEntity;

use Akeneo\EnrichedEntity\back\Application\EnrichedEntity\ListEnrichedEntityHandler;
use Akeneo\EnrichedEntity\back\Domain\Model\EnrichedEntity\EnrichedEntity;
use Akeneo\EnrichedEntity\back\Domain\Model\EnrichedEntity\EnrichedEntityIdentifier;
use Akeneo\EnrichedEntity\back\Domain\Repository\EnrichedEntityRepository;
use PhpSpec\ObjectBehavior;

class ListEnrichedEntityHandlerSpec extends ObjectBehavior
{
    public function let(EnrichedEntityRepository $repository)
    {
        $this->beConstructedWith($repository);
    }

    function it_is_initializable()
    {
        $this->shouldHaveType(ListEnrichedEntityHandler::class);
    }

    function it_returns_a_collection_of_enriched_entity_if_there_is_enriched_entities_in_database(
        $repository,
        EnrichedEntity $enrichedEntity
    ) {
        $repository->all()->willReturn([$enrichedEntity]);

        $this->__invoke()->shouldReturn([$enrichedEntity]);
    }

    function it_returns_null_if_there_enriched_entity_does_not_exist_for_the_given_identifier(
        $repository
    ) {
        $repository->all()->willReturn([]);

        $this->__invoke()->shouldReturn([]);
    }
}
