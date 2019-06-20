<?php

namespace spec\Akeneo\AssetManager\Application\Attribute\EditAttribute\CommandFactory;

use Akeneo\AssetManager\Application\Attribute\EditAttribute\CommandFactory\EditIsRichTextEditorCommand;
use Akeneo\AssetManager\Application\Attribute\EditAttribute\CommandFactory\EditIsRichTextEditorCommandFactory;
use PhpSpec\ObjectBehavior;

class EditIsRichTextEditorCommandFactorySpec extends ObjectBehavior
{
    function it_is_initializable()
    {
        $this->shouldHaveType(EditIsRichTextEditorCommandFactory::class);
    }

    function it_only_supports_attribute_property_is_rich_text_editor_edits()
    {
        $this->supports([
            'identifier'          => ['identifier' => 'name', 'asset_family_identifier' => 'designer'],
            'is_rich_text_editor' => true,
        ])->shouldReturn(true);
        $this->supports([
            'identifier'          => ['identifier' => 'name', 'asset_family_identifier' => 'designer'],
            'is_rich_text_editor' => null
        ])->shouldReturn(true);
        $this->supports([
            'identifier' => ['identifier' => 'name', 'asset_family_identifier' => 'designer'],
            'labels'     => 'wrong_property',
        ])->shouldReturn(false);
        $this->supports(['dummy' => 10])->shouldReturn(false);
    }

    function it_creates_a_command_to_edit_the_is_rich_text_editor_flag()
    {
        $command = $this->create([
            'identifier' => 'name',
            'is_rich_text_editor'   => true,
        ]);
        $command->shouldBeAnInstanceOf(EditIsRichTextEditorCommand::class);
        $command->identifier->shouldBeEqualTo('name');
        $command->isRichTextEditor->shouldBeEqualTo(true);
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
