<?php

namespace Akeneo\Pim\TailoredExport\Connector\Processor\ValueSelector;

use Akeneo\Pim\Enrichment\Component\Product\Model\ValueInterface;
use Akeneo\Pim\Structure\Component\Query\PublicApi\AttributeType\Attribute;

interface ValueSelectorInterface
{
    public function support(array $selection, Attribute $attribute);

    public function applySelection(array $selection, Attribute $attribute, ValueInterface $data): string;
}
