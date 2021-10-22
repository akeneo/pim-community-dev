<?php

namespace Specification\Akeneo\Pim\Enrichment\Bundle\PdfGeneration\Renderer\ProductValueRenderer;

use Akeneo\Pim\Enrichment\Component\Product\Model\ValueInterface;
use Akeneo\Pim\Structure\Component\AttributeTypes;
use Akeneo\Pim\Structure\Component\Model\AttributeInterface;
use PhpSpec\ObjectBehavior;
use Symfony\Contracts\Translation\TranslatorInterface;
use Twig\Environment;

class BooleanProductValueRendererSpec extends ObjectBehavior
{
    function let(
        TranslatorInterface $translator
    ) {
        $this->beConstructedWith($translator);
    }

    function it_supports_boolean_attributes()
    {
        $this->supportsAttributeType(AttributeTypes::BOOLEAN)->shouldReturn(true);
        $this->supportsAttributeType(AttributeTypes::TEXTAREA)->shouldReturn(false);
    }

    function it_renders_null_value(
        Environment $environment,
        AttributeInterface $attribute
    ) {
        $this->render($environment, $attribute, null, 'en_US')->shouldReturn(null);
    }

    function it_renders_boolean_value(
        TranslatorInterface $translator,
        Environment $environment,
        AttributeInterface $attribute,
        ValueInterface $value
    ) {
        $translator->trans('Yes')->shouldBeCalled()->willReturn('Vrai');
        $value->getData()->shouldBeCalled()->willReturn(true);
        $this->render($environment, $attribute, $value, 'en_US')->shouldReturn('Vrai');
    }
}
