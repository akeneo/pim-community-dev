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

use Akeneo\Platform\Syndication\Application\Common\Selection\PriceCollection\PriceCollectionAmountSelection;
use Akeneo\Platform\Syndication\Application\Common\Selection\SelectionInterface;
use Akeneo\Platform\Syndication\Application\Common\SourceValue\Price;
use Akeneo\Platform\Syndication\Application\Common\SourceValue\PriceCollectionValue;
use Akeneo\Platform\Syndication\Application\Common\SourceValue\SourceValueInterface;
use Akeneo\Platform\Syndication\Application\Common\Target\StringTarget;
use Akeneo\Platform\Syndication\Application\Common\Target\Target;
use Akeneo\Platform\Syndication\Application\MapValues\SelectionApplier\SelectionApplierInterface;

class PriceCollectionAmountSelectionApplier implements SelectionApplierInterface
{
    public function applySelection(SelectionInterface $selection, Target $target, SourceValueInterface $value): string
    {
        if (
            !$selection instanceof PriceCollectionAmountSelection
            || !$value instanceof PriceCollectionValue
        ) {
            throw new \InvalidArgumentException('Cannot apply Price collection selection on this entity');
        }

        $priceCollection = $value->getPriceCollection();
        $currencies = $selection->getCurrencies();
        if ($currencies) {
            $priceCollection = array_filter($priceCollection, static fn (Price $price) => in_array($price->getCurrency(), $currencies));
        }

        $selectedData = array_map(static fn (Price $price) => $price->getAmount(), $priceCollection);

        return implode($selection->getSeparator(), $selectedData);
    }

    public function supports(SelectionInterface $selection, Target $target, SourceValueInterface $value): bool
    {
        return $selection instanceof PriceCollectionAmountSelection
            && $value instanceof PriceCollectionValue
            && $target instanceof StringTarget;
    }
}
