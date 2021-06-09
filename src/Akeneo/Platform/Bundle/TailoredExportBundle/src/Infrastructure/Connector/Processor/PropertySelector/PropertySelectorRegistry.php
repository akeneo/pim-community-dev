<?php

namespace Akeneo\Platform\TailoredExport\Infrastructure\Connector\Processor\PropertySelector;

use Akeneo\Pim\Enrichment\Component\Product\Model\ValueInterface;
use Akeneo\Pim\Structure\Component\Query\PublicApi\AttributeType\Attribute;

class PropertySelectorRegistry
{
    private iterable $propertySelectors;

    public function __construct(iterable $propertySelectors)
    {
        $this->propertySelectors = $propertySelectors;
    }

    public function applyPropertySelection(array $selectionConfiguration, $data): string
    {
        foreach ($this->propertySelectors as $valueSelector) {
            if ($valueSelector->supports($selectionConfiguration)) {
                return $valueSelector->applySelection($selectionConfiguration, $data);
            }
        }

        throw new \Exception('No selection available');
    }
}
