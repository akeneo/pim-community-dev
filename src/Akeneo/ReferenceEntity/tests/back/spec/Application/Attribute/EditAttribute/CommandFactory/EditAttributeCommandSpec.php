<?php

namespace spec\Akeneo\ReferenceEntity\Application\Attribute\EditAttribute\CommandFactory;

use Akeneo\ReferenceEntity\Application\Attribute\EditAttribute\CommandFactory\EditAttributeCommand;
use Akeneo\ReferenceEntity\Application\Attribute\EditAttribute\CommandFactory\EditIsRequiredCommand;
use Akeneo\ReferenceEntity\Application\Attribute\EditAttribute\CommandFactory\EditIsRichTextEditorCommand;
use Akeneo\ReferenceEntity\Application\Attribute\EditAttribute\CommandFactory\EditLabelsCommand;
use PhpSpec\ObjectBehavior;

class EditAttributeCommandSpec extends ObjectBehavior
{
    function let()
    {
        $this->beConstructedWith('name_designer_fingerprint', []);
    }

    function it_is_initializable()
    {
        $this->shouldHaveType(EditAttributeCommand::class);
    }

    function it_finds_a_command_by_classname()
    {
        $editIsRichTextEditorCommand = new EditIsRichTextEditorCommand('name', false);
        $editLabelsCommand = new EditLabelsCommand('name', []);
        $this->editCommands = [$editIsRichTextEditorCommand, $editLabelsCommand];

        $this->findCommand(EditIsRichTextEditorCommand::class)->shouldReturn($editIsRichTextEditorCommand);
        $this->findCommand(EditLabelsCommand::class)->shouldReturn($editLabelsCommand);
        $this->findCommand(EditIsRequiredCommand::class)->shouldReturn(null);
    }
}
