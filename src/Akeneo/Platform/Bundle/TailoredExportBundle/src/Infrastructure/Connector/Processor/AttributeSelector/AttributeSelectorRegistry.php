<?php

declare(strict_types=1);

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2021 Akeneo SAS (https://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Akeneo\Platform\TailoredExport\Infrastructure\Connector\Processor\AttributeSelector;

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

    public function applyAttributeSelection(array $selectionConfiguration, $entity, Attribute $attribute, $value): string
    {
        if (!$value instanceof ValueInterface) {
            return $value ?? '';
        }

        foreach ($this->attributeSelectors as $valueSelector) {
            if ($valueSelector->supports($selectionConfiguration, $attribute)) {
                return $valueSelector->applySelection($selectionConfiguration, $entity, $attribute, $value);
            }
        }

        throw new \LogicException('No selection available for ' . $attribute->code());
    }
}
