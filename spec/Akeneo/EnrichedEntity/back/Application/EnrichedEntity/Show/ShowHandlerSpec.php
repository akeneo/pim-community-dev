<?php

namespace spec\Akeneo\EnrichedEntity\back\Application\EnrichedEntity\Show;

use Akeneo\EnrichedEntity\back\Application\EnrichedEntity\Show\ShowEnrichedEntityHandler;
use Akeneo\EnrichedEntity\back\Domain\Model\EnrichedEntity\EnrichedEntity;
use Akeneo\EnrichedEntity\back\Domain\Model\EnrichedEntity\EnrichedEntityIdentifier;
use Akeneo\EnrichedEntity\back\Domain\Repository\EnrichedEntityRepository;
use PhpSpec\ObjectBehavior;

class ShowEnrichedEntityHandlerSpec extends ObjectBehavior
{
    public function let(EnrichedEntityRepository $repository)
    {
        $this->beConstructedWith($repository);
    }

    function it_is_initializable()
    {
        $this->shouldHaveType(ShowEnrichedEntityHandler::class);
    }

    function it_returns_an_enriched_entity_given_its_identifier(
        $repository,
        EnrichedEntityIdentifier $identifier,
        EnrichedEntity $enrichedEntity
    ) {
        $repository->findOneByIdentifier($identifier)->willReturn($enrichedEntity);
        $this->show($identifier)->shouldReturn($enrichedEntity);
    }

    function it_returns_null_if_there_enriched_entity_does_not_exist_for_the_given_identifier(
        $repository,
        EnrichedEntityIdentifier $identifier
    ) {
        $repository->findOneByIdentifier($identifier)->willReturn(null);
        $this->show($identifier)->shouldReturn(null);
    }
}
