<?php

namespace spec\Akeneo\ReferenceEntity\Application\Attribute\EditAttribute\CommandFactory;

use Akeneo\ReferenceEntity\Application\Attribute\EditAttribute\CommandFactory\EditAllowedExtensionsCommand;
use Akeneo\ReferenceEntity\Application\Attribute\EditAttribute\CommandFactory\EditAllowedExtensionsCommandFactory;
use PhpSpec\ObjectBehavior;

class EditAllowedExtensionsCommandFactorySpec extends ObjectBehavior
{
    function it_is_initializable()
    {
        $this->shouldHaveType(EditAllowedExtensionsCommandFactory::class);
    }

    function it_only_supports_attribute_allowed_extension_edits()
    {
        $this->supports([
            'identifier'         => ['identifier' => 'portrait', 'reference_entity_identifier' => 'designer'],
            'allowed_extensions' => ['pdf', 'png'],
        ])->shouldReturn(true);
        $this->supports([
            'identifier'         => ['identifier' => 'portrait', 'reference_entity_identifier' => 'designer'],
            'allowed_extensions' => null,
        ])->shouldReturn(true);
        $this->supports([
            'identifier'    => ['identifier' => 'portrait', 'reference_entity_identifier' => 'designer'],
            'max_file_size' => '172.50',
        ])->shouldReturn(false);
        $this->supports(['dummy' => 10])->shouldReturn(false);
    }

    function it_creates_a_command_to_edit_the_allowed_extensions_of_a_attribute()
    {
        $command = $this->create([
            'identifier'         => 'portrait',
            'allowed_extensions' => ['pdf', 'png'],
        ]);
        $command->shouldBeAnInstanceOf(EditAllowedExtensionsCommand::class);
        $command->identifier->shouldBeEqualTo('portrait');
        $command->allowedExtensions->shouldBeEqualTo(['pdf', 'png']);
    }

    function it_throws_if_it_cannot_create_the_command()
    {
        $this->shouldThrow(\RuntimeException::class)
            ->during('create', [
                [
                    'identifier'     => 'portrait',
                    'wrong_property' => 10,
                ],
            ]);
    }
}
