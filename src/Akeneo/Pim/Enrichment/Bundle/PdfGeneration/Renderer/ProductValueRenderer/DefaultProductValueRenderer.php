<?php

namespace Akeneo\Pim\Enrichment\Bundle\PdfGeneration\Renderer\ProductValueRenderer;

use Akeneo\Pim\Enrichment\Component\Product\Model\ValueInterface;
use Akeneo\Pim\Structure\Component\Model\AttributeInterface;
use Twig\Environment;

class DefaultProductValueRenderer implements ProductValueRenderer
{
    public function render(Environment $environment, AttributeInterface $attribute, ?ValueInterface $value, string $localeCode): ?string
    {
        /** @phpstan-ignore-next-line */
        return \twig_escape_filter($environment, $value);
    }

    public function supportsAttributeType(string $attributeType): bool
    {
        return true;
    }
}
