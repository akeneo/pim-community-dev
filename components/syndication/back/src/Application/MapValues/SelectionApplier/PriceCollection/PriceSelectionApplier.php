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

namespace Akeneo\Platform\Syndication\Application\MapValues\SelectionApplier\PriceCollection;

use Akeneo\Platform\Syndication\Application\Common\Selection\PriceCollection\PriceSelection;
use Akeneo\Platform\Syndication\Application\Common\Selection\SelectionInterface;
use Akeneo\Platform\Syndication\Application\Common\SourceValue\Price;
use Akeneo\Platform\Syndication\Application\Common\SourceValue\PriceCollectionValue;
use Akeneo\Platform\Syndication\Application\Common\SourceValue\SourceValueInterface;
use Akeneo\Platform\Syndication\Application\Common\Target\PriceTarget;
use Akeneo\Platform\Syndication\Application\Common\Target\Target;
use Akeneo\Platform\Syndication\Application\MapValues\SelectionApplier\SelectionApplierInterface;

class PriceSelectionApplier implements SelectionApplierInterface
{
    public function applySelection(SelectionInterface $selection, Target $target, SourceValueInterface $value)
    {
        if (
            !$selection instanceof PriceSelection
            || !$value instanceof PriceCollectionValue
        ) {
            throw new \InvalidArgumentException('Cannot apply Price selection on this entity');
        }

        $priceCollection = $value->getPriceCollection();
        $currency = $selection->getCurrency();

        $matchingPrices = array_values(array_filter($priceCollection, static fn (Price $price) => $price->getCurrency() === $currency));

        if (count($matchingPrices) === 0) {
            return null;
        }

        $price = $matchingPrices[0];

        return [
            'amount' => $price->getAmount(),
            'currency' => $price->getCurrency(),
        ];
    }

    public function supports(SelectionInterface $selection, Target $target, SourceValueInterface $value): bool
    {
        return $selection instanceof PriceSelection
            && $value instanceof PriceCollectionValue
            && $target instanceof PriceTarget;
    }
}
