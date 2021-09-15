<?php

namespace Akeneo\Pim\Enrichment\Bundle\PdfGeneration\Renderer\ProductValueRenderer;

use Akeneo\Pim\Structure\Component\AttributeTypes;
use Akeneo\Pim\Structure\Component\Model\AttributeInterface;
use Twig\Environment;

class ImageProductValueRenderer implements ProductValueRenderer
{
    public function render(Environment $environment, AttributeInterface $attribute, $value): ?string
    {
        if (null !== $value && $value->getData() !== null) {
            return $value->getData()->getOriginalFilename();
        }

        return null;
    }

    public function supportsAttributeType(string $attributeType): bool
    {
        return $attributeType === AttributeTypes::IMAGE;
    }
}
