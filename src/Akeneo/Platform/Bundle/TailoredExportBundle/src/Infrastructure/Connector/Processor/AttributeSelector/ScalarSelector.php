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
