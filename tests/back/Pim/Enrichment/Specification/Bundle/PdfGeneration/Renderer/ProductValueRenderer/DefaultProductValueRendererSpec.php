<?php

namespace Specification\Akeneo\Pim\Enrichment\Bundle\PdfGeneration\Renderer\ProductValueRenderer;

use Akeneo\Pim\Enrichment\Component\Product\Model\ValueInterface;
use Akeneo\Pim\Enrichment\Component\Product\Value\MetricValue;
use Akeneo\Pim\Structure\Component\AttributeTypes;
use Akeneo\Pim\Structure\Component\Model\Attribute;
use PhpSpec\ObjectBehavior;
use Twig\Environment;
use Twig\Loader\LoaderInterface;

class DefaultProductValueRendererSpec extends ObjectBehavior
{
    function it_supports_every_attributes()
    {
        $this->supportsAttributeType(AttributeTypes::BOOLEAN)->shouldReturn(true);
        $this->supportsAttributeType(AttributeTypes::TEXTAREA)->shouldReturn(true);
    }

    function it_renders_string_value(ValueInterface $value, LoaderInterface $loader)
    {
        $environment = new Environment($loader->getWrappedObject());
        $attribute = new Attribute();

        $value->__toString()
            ->shouldBeCalled()
            ->willReturn('a value');

        $this->render($environment, $attribute, $value, 'en_US')->shouldReturn('a value');
    }

    function it_renders_metric_value(MetricValue $value, LoaderInterface $loader)
    {
        $environment = new Environment($loader->getWrappedObject());
        $attribute = new Attribute();

        $value->__toString()
            ->shouldBeCalled()
            ->willReturn('100 GRAM');

        $this->render($environment, $attribute, $value, 'en_US')->shouldReturn('100 GRAM');
    }
}
