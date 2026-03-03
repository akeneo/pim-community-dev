<?php

namespace Akeneo\Pim\Enrichment\Bundle\PdfGeneration\Renderer\ProductValueRenderer;

class ProductValueRendererRegistry
{
    /** @var ProductValueRenderer[] */
    private iterable $renderers = [];

    private ProductValueRenderer $defaultRenderer;

    /**
     * @param ProductValueRenderer[] $renderers
     */
    public function __construct(iterable $renderers, ProductValueRenderer $defaultRenderer)
    {
        $this->renderers = $renderers;
        $this->defaultRenderer = $defaultRenderer;
    }

    public function getProductValueRenderer($attributeType): ProductValueRenderer
    {
        foreach ($this->renderers as $renderer) {
            if ($renderer->supportsAttributeType($attributeType)) {
                return $renderer;
            }
        }

        return $this->defaultRenderer;
    }
}
