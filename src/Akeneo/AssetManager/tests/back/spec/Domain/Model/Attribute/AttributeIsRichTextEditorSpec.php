<?php

namespace spec\Akeneo\AssetManager\Domain\Model\Attribute;

use Akeneo\AssetManager\Domain\Model\Attribute\AttributeIsRichTextEditor;
use PhpSpec\ObjectBehavior;

class AttributeIsRichTextEditorSpec extends ObjectBehavior
{
    function let()
    {
        $this->beConstructedThrough('fromBoolean', [true]);
    }

    function it_is_initializable()
    {
        $this->shouldHaveType(AttributeIsRichTextEditor::class);
    }

    function it_tells_if_it_is_yes()
    {
        $this->isYes()->shouldReturn(true);
    }

    function it_normalizes_itself()
    {
        $this->normalize()->shouldReturn(true);
    }
}
