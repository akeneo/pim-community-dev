<?php

namespace Specification\Akeneo\Pim\Enrichment\Bundle\PdfGeneration\Renderer\ProductValueRenderer;

use Akeneo\Pim\Enrichment\Component\Product\Model\ValueInterface;
use Akeneo\Pim\Structure\Component\AttributeTypes;
use Akeneo\Pim\Structure\Component\Model\Attribute;
use Akeneo\Pim\Structure\Component\Model\AttributeInterface;
use PhpSpec\ObjectBehavior;
use Symfony\Contracts\Translation\TranslatorInterface;
use Twig\Environment;

class TextareaProductValueRendererSpec extends ObjectBehavior
{
    function it_supports_textarea_attributes()
    {
        $this->supportsAttributeType(AttributeTypes::TEXTAREA)->shouldReturn(true);
        $this->supportsAttributeType(AttributeTypes::BOOLEAN)->shouldReturn(false);
    }

    function it_does_not_escape_value() {
        $environment = new Environment();
        $attribute = new Attribute();
        $attribute->setWysiwygEnabled(true);

        $this->render($environment, $attribute, '<div>a text</div>')->shouldReturn('<div>a text</div>');
    }

    function it_escapes_value() {
        $environment = new Environment();
        $attribute = new Attribute();
        $attribute->setWysiwygEnabled(false);

        $this->render($environment, $attribute, '<div>a text</div>')->shouldReturn('&lt;div&gt;a text&lt;/div&gt;');
    }
}
