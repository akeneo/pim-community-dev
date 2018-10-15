<?php

namespace spec\Akeneo\ReferenceEntity\Application\ReferenceEntity\CreateReferenceEntity;

use Akeneo\ReferenceEntity\Application\ReferenceEntity\CreateReferenceEntity\CreateReferenceEntityCommand;
use Akeneo\ReferenceEntity\Application\ReferenceEntity\CreateReferenceEntity\CreateReferenceEntityHandler;
use Akeneo\ReferenceEntity\Domain\Model\ReferenceEntity\ReferenceEntity;
use Akeneo\ReferenceEntity\Domain\Repository\ReferenceEntityRepositoryInterface;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;

class CreateReferenceEntityHandlerSpec extends ObjectBehavior
{
    function let(ReferenceEntityRepositoryInterface $referenceEntityRepository)
    {
        $this->beConstructedWith($referenceEntityRepository);
    }

    function it_is_initializable()
    {
        $this->shouldHaveType(CreateReferenceEntityHandler::class);
    }

    function it_creates_a_new_record(
        ReferenceEntityRepositoryInterface $referenceEntityRepository,
        CreateReferenceEntityCommand $createReferenceEntityCommand
    ) {
        $createReferenceEntityCommand->code = 'brand';
        $createReferenceEntityCommand->labels = [
            'en_US' => 'Intel',
            'fr_FR' => 'Intel',
        ];

        $referenceEntityRepository->create(Argument::type(ReferenceEntity::class))->shouldBeCalled();

        $this->__invoke($createReferenceEntityCommand);
    }
}
