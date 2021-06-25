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
