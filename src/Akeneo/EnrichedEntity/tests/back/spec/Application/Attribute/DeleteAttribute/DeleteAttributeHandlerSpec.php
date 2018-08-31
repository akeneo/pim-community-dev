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
        $command->attributeIdentifier = 'name_designer_test';

        $identifier = AttributeIdentifier::fromString('name_designer_test');

        $repository->deleteByIdentifier($identifier)->shouldBeCalled();

        $this->__invoke($command);
    }
}
