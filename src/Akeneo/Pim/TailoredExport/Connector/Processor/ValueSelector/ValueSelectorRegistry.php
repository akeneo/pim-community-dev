<?php

namespace Akeneo\Pim\TailoredExport\Connector\Processor\ValueSelector;

use Akeneo\Pim\Enrichment\Component\Product\Model\ValueInterface;
use Akeneo\Pim\Structure\Component\Query\PublicApi\AttributeType\Attribute;

class ValueSelectorRegistry
{
    /** @var ValueSelectorInterface[] */
    private iterable $valueSelectors;

    public function __construct(iterable $valueSelectors)
    {
        $this->valueSelectors = $valueSelectors;
    }

    public function applySelection(array $selection, Attribute $attribute, $data): string
    {
        if (!$data instanceof ValueInterface) {
            return $data ?? '';
        }

        foreach ($this->valueSelectors as $valueSelector) {
            if ($valueSelector->support($selection, $attribute)) {
                return $valueSelector->applySelection($selection, $attribute, $data);
            }
        }

        throw new \Exception('No selection available');
    }
}
