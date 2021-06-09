<?php

declare(strict_types=1);

namespace Akeneo\Platform\TailoredExport\Infrastructure\Connector\Processor\AttributeSelector;

use Akeneo\Pim\Enrichment\Component\Product\Model\ValueInterface;
use Akeneo\Pim\Structure\Component\Query\PublicApi\AttributeType\Attribute;

class ScalarSelector implements AttributeSelectorInterface
{
    private array $supportedAttributeTypes;

    /**
     * @param string[] $supportedAttributeTypes
     */
    public function __construct(array $supportedAttributeTypes)
    {
        $this->supportedAttributeTypes = $supportedAttributeTypes;
    }

    public function applySelection(array $selectionConfiguration, Attribute $attribute, ValueInterface $value): string
    {
        return (string) $value->getData();
    }

    public function supports(array $selectionConfiguration, Attribute $attribute): bool
    {
        return in_array($attribute->type(), $this->supportedAttributeTypes);
    }
}
