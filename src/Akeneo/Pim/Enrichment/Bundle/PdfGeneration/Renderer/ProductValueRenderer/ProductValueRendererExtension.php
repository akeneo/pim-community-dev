<?php

namespace Akeneo\Pim\Enrichment\Bundle\PdfGeneration\Renderer\ProductValueRenderer;

use Akeneo\Pim\Enrichment\Component\Product\Model\ValueInterface;
use Akeneo\Pim\Structure\Component\Model\AttributeInterface;
use Twig\Environment;
use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;

class ProductValueRendererExtension extends AbstractExtension
{
    private ProductValueRendererRegistry $productValueRendererRegistry;

    public function __construct(
        ProductValueRendererRegistry $productValueRendererRegistry
    ) {
        $this->productValueRendererRegistry = $productValueRendererRegistry;
    }

    public function getFunctions(): array
    {
        return [
            new TwigFunction('render_attribute_value', [$this, 'renderAttributeValue'], ['needs_environment' => true]),
        ];
    }

    public function renderAttributeValue(Environment $environment, AttributeInterface $attribute, ?ValueInterface $productValue, string $localeCode): ?string
    {
        return $this->productValueRendererRegistry
            ->getProductValueRenderer($attribute->getType())
            ->render($environment, $attribute, $productValue, $localeCode);
    }
}
