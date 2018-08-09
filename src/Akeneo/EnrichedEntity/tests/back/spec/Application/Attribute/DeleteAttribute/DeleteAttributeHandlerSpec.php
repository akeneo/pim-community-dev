<?php

namespace spec\Akeneo\EnrichedEntity\Application\Attribute\DeleteAttribute;

use Akeneo\EnrichedEntity\Application\Attribute\DeleteAttribute\DeleteAttributeCommand;
use Akeneo\EnrichedEntity\Application\Attribute\DeleteAttribute\DeleteAttributeHandler;
use Akeneo\EnrichedEntity\Domain\Model\Attribute\AttributeIdentifier;
use Akeneo\EnrichedEntity\Domain\Repository\AttributeRepositoryInterface;
use PhpSpec\ObjectBehavior;

class DeleteAttributeHandlerSpec extends ObjectBehavior
{
    public function let(AttributeRepositoryInterface $repository)
    {
        $this->beConstructedWith($repository);
    }

    function it_is_initializable()
    {
        $this->shouldHaveType(DeleteAttributeHandler::class);
    }

    function it_deletes_an_attribute_by_its_identifier(AttributeRepositoryInterface $repository)
    {
        $command = new DeleteAttributeCommand();
        $command->enrichedEntityIdentifier = 'designer';
        $command->identifier = 'name';

        $identifier = AttributeIdentifier::create(
            $command->enrichedEntityIdentifier,
            $command->identifier
        );

        $repository->deleteByIdentifier($identifier)->shouldBeCalled();

        $this->__invoke($command);
    }
}
