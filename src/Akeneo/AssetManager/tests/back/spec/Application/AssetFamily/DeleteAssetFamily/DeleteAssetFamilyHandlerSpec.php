<?php

declare(strict_types=1);

namespace spec\Akeneo\ReferenceEntity\Application\ReferenceEntity\DeleteReferenceEntity;

use Akeneo\ReferenceEntity\Application\ReferenceEntity\DeleteReferenceEntity\DeleteReferenceEntityCommand;
use Akeneo\ReferenceEntity\Application\ReferenceEntity\DeleteReferenceEntity\DeleteReferenceEntityHandler;
use Akeneo\ReferenceEntity\Domain\Model\ReferenceEntity\ReferenceEntityIdentifier;
use Akeneo\ReferenceEntity\Domain\Repository\ReferenceEntityRepositoryInterface;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;

class DeleteReferenceEntityHandlerSpec extends ObjectBehavior
{
    function let(ReferenceEntityRepositoryInterface $referenceEntityRepository)
    {
        $this->beConstructedWith($referenceEntityRepository);
    }

    function it_is_initializable()
    {
        $this->shouldHaveType(DeleteReferenceEntityHandler::class);
    }

    function it_deletes_a_reference_entity(
        ReferenceEntityRepositoryInterface $referenceEntityRepository,
        DeleteReferenceEntityCommand $command
    ) {
        $command->identifier = 'brand';

        $referenceEntityRepository->deleteByIdentifier(
            Argument::type(ReferenceEntityIdentifier::class)
        )->shouldBeCalled();

        $this->__invoke($command);
    }
}
