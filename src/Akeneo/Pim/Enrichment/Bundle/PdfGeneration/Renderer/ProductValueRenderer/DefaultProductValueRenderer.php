<?php

namespace Akeneo\Pim\Enrichment\Bundle\PdfGeneration\Renderer\ProductValueRenderer;

use Akeneo\Pim\Structure\Component\Model\AttributeInterface;
use Twig\Environment;

class DefaultProductValueRenderer implements ProductValueRenderer
{
    public function render(Environment $environment, AttributeInterface $attribute, $value): ?string
    {
        return \twig_escape_filter($environment, $value);
    }

    public function supportsAttributeType(string $attributeType): bool
    {
        return true;
    }
}
