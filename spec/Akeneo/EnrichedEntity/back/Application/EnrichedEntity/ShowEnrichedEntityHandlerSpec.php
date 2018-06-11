<?php

namespace spec\Akeneo\EnrichedEntity\back\Application\EnrichedEntity;

use Akeneo\EnrichedEntity\back\Application\EnrichedEntity\ShowEnrichedEntityHandler;
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
        EnrichedEntity $enrichedEntity
    ) {
        $identifier = EnrichedEntityIdentifier::fromString('designer');
        $repository->findOneByIdentifier($identifier)->willReturn($enrichedEntity);

        $this->__invoke('designer')->shouldReturn($enrichedEntity);
    }

    function it_returns_null_if_there_enriched_entity_does_not_exist_for_the_given_identifier(
        $repository
    ) {
        $identifier = EnrichedEntityIdentifier::fromString('sofa');
        $repository->findOneByIdentifier($identifier)->willReturn(null);

        $this->__invoke($identifier)->shouldReturn(null);
    }
}
