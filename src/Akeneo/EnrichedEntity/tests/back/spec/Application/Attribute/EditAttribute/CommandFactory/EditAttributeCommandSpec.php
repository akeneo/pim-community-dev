<?php

namespace spec\Akeneo\EnrichedEntity\Application\Attribute\EditAttribute\CommandFactory;

use Akeneo\EnrichedEntity\Application\Attribute\EditAttribute\CommandFactory\EditAttributeCommand;
use Akeneo\EnrichedEntity\Application\Attribute\EditAttribute\CommandFactory\EditIsRequiredCommand;
use Akeneo\EnrichedEntity\Application\Attribute\EditAttribute\CommandFactory\EditIsRichTextEditorCommand;
use Akeneo\EnrichedEntity\Application\Attribute\EditAttribute\CommandFactory\EditLabelsCommand;
use PhpSpec\ObjectBehavior;

class EditAttributeCommandSpec extends ObjectBehavior
{
    function it_is_initializable()
    {
        $this->shouldHaveType(EditAttributeCommand::class);
    }

    function it_finds_a_command_by_classname()
    {
        $editIsRichTextEditorCommand = new EditIsRichTextEditorCommand();
        $editLabelsCommand = new EditLabelsCommand();
        $this->editCommands = [$editIsRichTextEditorCommand, $editLabelsCommand];

        $this->findCommand(EditIsRichTextEditorCommand::class)->shouldReturn($editIsRichTextEditorCommand);
        $this->findCommand(EditLabelsCommand::class)->shouldReturn($editLabelsCommand);
        $this->findCommand(EditIsRequiredCommand::class)->shouldReturn(null);
    }
}
