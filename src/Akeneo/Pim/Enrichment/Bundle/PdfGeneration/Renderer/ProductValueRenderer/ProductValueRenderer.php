<?php

namespace Akeneo\Pim\Enrichment\Bundle\PdfGeneration\Renderer\ProductValueRenderer;

use Akeneo\Pim\Structure\Component\Model\AttributeInterface;
use Twig\Environment;

interface ProductValueRenderer {
    public function render(Environment $environment, AttributeInterface $attribute, $value): ?string;
    public function supports($attributeType): bool;
}
