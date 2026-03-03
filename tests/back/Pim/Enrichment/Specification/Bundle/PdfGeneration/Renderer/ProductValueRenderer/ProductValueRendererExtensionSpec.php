<?php

namespace Specification\Akeneo\Pim\Enrichment\Bundle\PdfGeneration\Renderer\ProductValueRenderer;

use Akeneo\Pim\Enrichment\Bundle\PdfGeneration\Renderer\ProductValueRenderer\ProductValueRenderer;
use Akeneo\Pim\Enrichment\Bundle\PdfGeneration\Renderer\ProductValueRenderer\ProductValueRendererRegistry;
use Akeneo\Pim\Enrichment\Component\Product\Value\MetricValue;
use Akeneo\Pim\Structure\Component\Model\AttributeInterface;
use PhpSpec\ObjectBehavior;
use Twig\Environment;

class ProductValueRendererExtensionSpec extends ObjectBehavior
{
    function let(ProductValueRendererRegistry $productValueRendererRegistry)
    {
        $this->beConstructedWith($productValueRendererRegistry);
    }

    function it_renders_product_value(
        ProductValueRendererRegistry $productValueRendererRegistry,
        ProductValueRenderer $productValueRenderer,
        Environment $environment,
        AttributeInterface $attribute,
        MetricValue $value
    ) {
        $attribute
            ->getType()
            ->shouldBeCalled()
            ->willReturn('pim_catalog_metric');

        $productValueRendererRegistry
            ->getProductValueRenderer('pim_catalog_metric')
            ->shouldBeCalled()
            ->willReturn($productValueRenderer);

        $productValueRenderer
            ->render($environment, $attribute, $value, 'en_US')
            ->shouldBeCalled()
            ->willReturn('100 GRAM');

        $this->renderAttributeValue($environment, $attribute, $value, 'en_US')->shouldReturn('100 GRAM');
    }
}
