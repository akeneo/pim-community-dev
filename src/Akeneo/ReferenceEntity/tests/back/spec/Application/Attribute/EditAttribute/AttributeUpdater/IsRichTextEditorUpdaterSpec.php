<?php

namespace spec\Akeneo\ReferenceEntity\Application\Attribute\EditAttribute\AttributeUpdater;

use Akeneo\ReferenceEntity\Application\Attribute\EditAttribute\AttributeUpdater\IsRichTextEditorUpdater;
use Akeneo\ReferenceEntity\Application\Attribute\EditAttribute\CommandFactory\EditIsRichTextEditorCommand;
use Akeneo\ReferenceEntity\Application\Attribute\EditAttribute\CommandFactory\EditLabelsCommand;
use Akeneo\ReferenceEntity\Domain\Model\Attribute\AttributeIsRichTextEditor;
use Akeneo\ReferenceEntity\Domain\Model\Attribute\ImageAttribute;
use Akeneo\ReferenceEntity\Domain\Model\Attribute\TextAttribute;
use PhpSpec\ObjectBehavior;

class IsRichTextEditorUpdaterSpec extends ObjectBehavior
{
    function it_is_initializable()
    {
        $this->shouldHaveType(IsRichTextEditorUpdater::class);
    }

    function it_only_supports_edit_is_rich_text_editor_command_of_text_attributes(
        TextAttribute $textAttribute,
        ImageAttribute $imageAttribute
    ) {
        $isRichTextEditorEditCommand = new EditIsRichTextEditorCommand('name', true);
        $labelEditCommand = new EditLabelsCommand('name', []);

        $this->supports($textAttribute, $isRichTextEditorEditCommand)->shouldReturn(true);
        $this->supports($textAttribute, $labelEditCommand)->shouldReturn(false);
        $this->supports($imageAttribute, $isRichTextEditorEditCommand)->shouldReturn(false);
    }

    function it_edits_the_is_rich_text_editor_flag_of_a_text_attribute(TextAttribute $textAttribute)
    {
        $editIsRichTextEditor = new EditIsRichTextEditorCommand('name', true);
        $editIsRichTextEditor->isRichTextEditor = true;
        $textAttribute->setIsRichTextEditor(AttributeIsRichTextEditor::fromBoolean(true))->shouldBeCalled();
        $this->__invoke($textAttribute, $editIsRichTextEditor)->shouldReturn($textAttribute);
    }

    function it_throws_if_it_cannot_update_the_attribute(TextAttribute $rightAttribute, ImageAttribute $wrongAttribute)
    {
        $wrongCommand = new EditLabelsCommand('name', []);
        $rightCommand = new EditIsRichTextEditorCommand('name', true);
        $this->shouldThrow(\RuntimeException::class)->during('__invoke', [$rightAttribute, $wrongCommand]);
        $this->shouldThrow(\RuntimeException::class)->during('__invoke', [$wrongAttribute, $wrongCommand]);
        $this->shouldThrow(\RuntimeException::class)->during('__invoke', [$wrongAttribute, $rightCommand]);
    }
}
