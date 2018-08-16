<?php

declare(strict_types=1);

namespace spec\Akeneo\EnrichedEntity\Application\EnrichedEntity\DeleteEnrichedEntity;

use Akeneo\EnrichedEntity\Application\EnrichedEntity\DeleteEnrichedEntity\DeleteEnrichedEntityCommand;
use Akeneo\EnrichedEntity\Application\EnrichedEntity\DeleteEnrichedEntity\DeleteEnrichedEntityHandler;
use Akeneo\EnrichedEntity\Domain\Model\EnrichedEntity\EnrichedEntityIdentifier;
use Akeneo\EnrichedEntity\Domain\Repository\EnrichedEntityRepositoryInterface;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;

class DeleteEnrichedEntityHandlerSpec extends ObjectBehavior
{
    function let(EnrichedEntityRepositoryInterface $enrichedEntityRepository)
    {
        $this->beConstructedWith($enrichedEntityRepository);
    }

    function it_is_initializable()
    {
        $this->shouldHaveType(DeleteEnrichedEntityHandler::class);
    }

    function it_deletes_an_enriched_entity(
        EnrichedEntityRepositoryInterface $enrichedEntityRepository,
        DeleteEnrichedEntityCommand $command
    ) {
        $command->identifier = 'brand';

        $enrichedEntityRepository->deleteByIdentifier(
            Argument::type(EnrichedEntityIdentifier::class)
        )->shouldBeCalled();

        $this->__invoke($command);
    }
}
