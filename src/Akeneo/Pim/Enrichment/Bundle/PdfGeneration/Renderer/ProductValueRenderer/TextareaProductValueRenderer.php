<?php

namespace Akeneo\Pim\Enrichment\Bundle\PdfGeneration\Renderer\ProductValueRenderer;

use Akeneo\Pim\Structure\Component\AttributeTypes;
use Akeneo\Pim\Structure\Component\Model\AttributeInterface;
use Twig\Environment;

class TextareaProductValueRenderer implements ProductValueRenderer
{
    public function render(Environment $environment, AttributeInterface $attribute, $value): ?string
    {
        if ($attribute->isWysiwygEnabled()) {
            return $value;
        }

        return twig_escape_filter($environment, $value);
    }

    public function supports($attributeType): bool
    {
        return $attributeType === AttributeTypes::TEXTAREA;
    }
}
