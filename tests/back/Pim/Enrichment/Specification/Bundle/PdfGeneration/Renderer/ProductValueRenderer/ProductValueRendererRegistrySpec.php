<?php

namespace Specification\Akeneo\Pim\Enrichment\Bundle\PdfGeneration\Renderer\ProductValueRenderer;

use Akeneo\Pim\Enrichment\Bundle\PdfGeneration\Renderer\ProductValueRenderer\ProductValueRenderer;
use PhpSpec\ObjectBehavior;

class ProductValueRendererRegistrySpec extends ObjectBehavior
{
    function let(ProductValueRenderer $booleanRenderer, ProductValueRenderer $defaultRenderer)
    {
        $this->beConstructedWith([$booleanRenderer], $defaultRenderer);
    }

    function it_returns_matching_renderer(
        ProductValueRenderer $booleanRenderer
    ) {
        $booleanRenderer
            ->supportsAttributeType('pim_catalog_boolean')
            ->willReturn(true);

        $this->getProductValueRenderer('pim_catalog_boolean')->shouldReturn($booleanRenderer);
    }

    function it_returns_default_renderer(
        ProductValueRenderer $booleanRenderer,
        ProductValueRenderer $defaultRenderer
    ) {
        $booleanRenderer
            ->supportsAttributeType('pim_catalog_other')
            ->willReturn(false);

        $this->getProductValueRenderer('pim_catalog_other')->shouldReturn($defaultRenderer);
    }
}
