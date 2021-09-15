<?php

namespace Specification\Akeneo\Pim\Enrichment\Bundle\PdfGeneration\Renderer\ProductValueRenderer;

use Akeneo\Pim\Enrichment\Component\Product\Value\MetricValue;
use Akeneo\Pim\Structure\Component\AttributeTypes;
use Akeneo\Pim\Structure\Component\Model\Attribute;
use PhpSpec\ObjectBehavior;
use Twig\Environment;

class DefaultProductValueRendererSpec extends ObjectBehavior
{
    function it_supports_every_attributes()
    {
        $this->supportsAttributeType(AttributeTypes::BOOLEAN)->shouldReturn(true);
        $this->supportsAttributeType(AttributeTypes::TEXTAREA)->shouldReturn(true);
    }

    function it_renders_string_value()
    {
        $environment = new Environment();
        $attribute = new Attribute();
        $this->render($environment, $attribute, 'a value')->shouldReturn('a value');
    }

    function it_renders_metric_value(MetricValue $value)
    {
        $environment = new Environment();
        $attribute = new Attribute();
        $value->__toString()->willReturn('100 GRAM');
        $this->render($environment, $attribute, $value)->shouldReturn('100 GRAM');
    }
}
