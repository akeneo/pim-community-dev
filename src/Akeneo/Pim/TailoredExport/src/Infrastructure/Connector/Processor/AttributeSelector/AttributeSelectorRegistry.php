<?php

declare(strict_types=1);

namespace Akeneo\Pim\TailoredExport\Infrastructure\Connector\Processor\AttributeSelector;

use Akeneo\Pim\Enrichment\Component\Product\Model\ValueInterface;
use Akeneo\Pim\Structure\Component\Query\PublicApi\AttributeType\Attribute;

class AttributeSelectorRegistry
{
    /** @var iterable<AttributeSelectorInterface> */
    private iterable $attributeSelectors;

    public function __construct(iterable $attributeSelectors)
    {
        $this->attributeSelectors = $attributeSelectors;
    }

    public function applyAttributeSelection(array $selectionConfiguration, Attribute $attribute, $value): string
    {
        if (!$value instanceof ValueInterface) {
            return $value ?? '';
        }

        foreach ($this->attributeSelectors as $valueSelector) {
            if ($valueSelector->supports($selectionConfiguration, $attribute)) {
                return $valueSelector->applySelection($selectionConfiguration, $attribute, $value);
            }
        }

        throw new \Exception('No selection available for ' . $attribute->code());
    }
}
