<?php

namespace Specification\Akeneo\Pim\Enrichment\Bundle\PdfGeneration\Renderer\ProductValueRenderer;

use Akeneo\Pim\Enrichment\Component\Product\Model\ValueInterface;
use Akeneo\Pim\Structure\Component\AttributeTypes;
use Akeneo\Pim\Structure\Component\Model\Attribute;
use PhpSpec\ObjectBehavior;
use Twig\Environment;
use Twig\Loader\LoaderInterface;

class TextareaProductValueRendererSpec extends ObjectBehavior
{
    function it_supports_textarea_attributes()
    {
        $this->supportsAttributeType(AttributeTypes::TEXTAREA)->shouldReturn(true);
        $this->supportsAttributeType(AttributeTypes::BOOLEAN)->shouldReturn(false);
    }

    function it_does_not_escape_value(ValueInterface $value, LoaderInterface $loader)
    {
        $environment = new Environment($loader->getWrappedObject());
        $attribute = new Attribute();
        $attribute->setWysiwygEnabled(true);

        $value
            ->getData()
            ->shouldBeCalled()
            ->willReturn('<div>a text</div>');

        $this->render($environment, $attribute, $value, 'en_US')->shouldReturn('<div>a text</div>');
    }

    function it_escapes_value(ValueInterface $value, LoaderInterface $loader)
    {
        $environment = new Environment($loader->getWrappedObject());
        $attribute = new Attribute();
        $attribute->setWysiwygEnabled(false);

        $value
            ->__toString()
            ->shouldBeCalled()
            ->willReturn('<div>a text</div>');

        $this->render($environment, $attribute, $value, 'en_US')->shouldReturn('&lt;div&gt;a text&lt;/div&gt;');
    }
}
