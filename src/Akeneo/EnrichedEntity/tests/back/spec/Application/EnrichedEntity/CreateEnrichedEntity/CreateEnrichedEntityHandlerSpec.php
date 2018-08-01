<?php

namespace spec\Akeneo\EnrichedEntity\Application\EnrichedEntity\CreateEnrichedEntity;

use Akeneo\EnrichedEntity\Application\EnrichedEntity\CreateEnrichedEntity\CreateEnrichedEntityCommand;
use Akeneo\EnrichedEntity\Application\EnrichedEntity\CreateEnrichedEntity\CreateEnrichedEntityHandler;
use Akeneo\EnrichedEntity\Domain\Model\EnrichedEntity\EnrichedEntity;
use Akeneo\EnrichedEntity\Domain\Repository\EnrichedEntityRepositoryInterface;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;

class CreateEnrichedEntityHandlerSpec extends ObjectBehavior
{
    function let(EnrichedEntityRepositoryInterface $enrichedEntityRepository)
    {
        $this->beConstructedWith($enrichedEntityRepository);
    }

    function it_is_initializable()
    {
        $this->shouldHaveType(CreateEnrichedEntityHandler::class);
    }

    function it_creates_a_new_record(
        EnrichedEntityRepositoryInterface $enrichedEntityRepository,
        CreateEnrichedEntityCommand $createEnrichedEntityCommand
    ) {
        $createEnrichedEntityCommand->identifier = 'brand';
        $createEnrichedEntityCommand->labels = [
            'en_US' => 'Intel',
            'fr_FR' => 'Intel',
        ];

        $enrichedEntityRepository->create(Argument::type(EnrichedEntity::class))->shouldBeCalled();

        $this->__invoke($createEnrichedEntityCommand);
    }
}
