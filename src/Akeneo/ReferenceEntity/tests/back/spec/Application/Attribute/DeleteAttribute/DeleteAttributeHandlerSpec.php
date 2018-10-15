<?php

namespace spec\Akeneo\ReferenceEntity\Application\Attribute\DeleteAttribute;

use Akeneo\ReferenceEntity\Application\Attribute\DeleteAttribute\DeleteAttributeCommand;
use Akeneo\ReferenceEntity\Application\Attribute\DeleteAttribute\DeleteAttributeHandler;
use Akeneo\ReferenceEntity\Domain\Model\Attribute\AttributeIdentifier;
use Akeneo\ReferenceEntity\Domain\Repository\AttributeRepositoryInterface;
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
