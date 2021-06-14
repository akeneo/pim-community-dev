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

use Akeneo\Pim\Enrichment\Component\Product\Model\ProductPriceInterface;
use Akeneo\Pim\Enrichment\Component\Product\Model\ValueInterface;
use Akeneo\Pim\Structure\Component\Query\PublicApi\AttributeType\Attribute;
use Akeneo\Platform\TailoredExport\Domain\SelectionTypes;

/**
 * @author Pierre Jolly <pierre.jolly@akeneo.com>
 */
class PriceCollectionSelector implements AttributeSelectorInterface
{
    /** @var string[] */
    private array $supportedAttributeTypes;

    public function __construct(array $supportedAttributeTypes)
    {
        $this->supportedAttributeTypes = $supportedAttributeTypes;
    }

    public function applySelection(array $selectionConfiguration, Attribute $attribute, ValueInterface $value): string
    {
        $priceCollection = $value->getData()->toArray();
        switch ($selectionConfiguration['type']) {
            case SelectionTypes::AMOUNT:
                $selectedData = array_map(fn (ProductPriceInterface $price) => $price->getData(), $priceCollection);
                break;
            case SelectionTypes::CURRENCY:
                $selectedData = array_map(fn (ProductPriceInterface $price) => $price->getCurrency(), $priceCollection);
                break;
            default:
                throw new \LogicException(sprintf('Selection type "%s" is not supported', $selectionConfiguration['type']));
        }

        return implode(', ', $selectedData);
    }

    public function supports(array $selectionConfiguration, Attribute $attribute): bool
    {
        return in_array($selectionConfiguration['type'], [SelectionTypes::AMOUNT, SelectionTypes::CURRENCY])
            && in_array($attribute->type(), $this->supportedAttributeTypes);
    }
}
