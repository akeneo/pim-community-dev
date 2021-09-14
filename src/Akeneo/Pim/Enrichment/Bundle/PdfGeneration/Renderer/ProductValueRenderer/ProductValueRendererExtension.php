<?php

namespace Akeneo\Pim\Enrichment\Bundle\PdfGeneration\Renderer\ProductValueRenderer;

use Akeneo\Pim\Structure\Component\Model\AttributeInterface;
use Twig\Environment;

class ProductValueRendererExtension extends \Twig_Extension
{
    private ProductValueRendererRegistry $productValueRendererRegistry;

    public function __construct(
        ProductValueRendererRegistry $productValueRendererRegistry
    ) {
        $this->productValueRendererRegistry = $productValueRendererRegistry;
    }

    public function getFunctions()
    {
        return [
            new \Twig_SimpleFunction('render_attribute_value', [$this, 'renderAttributeValue'], ['needs_environment' => true]),
        ];
    }

    public function renderAttributeValue(Environment $environment, AttributeInterface $attribute, $attributeValue)
    {
        $renderer = $this->productValueRendererRegistry->getProductValueRenderer($attribute->getType());
        if (null === $renderer) {
            return null;
        }

        return $renderer->render($environment, $attribute, $attributeValue);
    }
}
