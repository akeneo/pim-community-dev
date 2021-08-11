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

namespace Akeneo\Platform\TailoredExport\Application\MapValues\SelectionApplier\PriceCollection;

use Akeneo\Platform\TailoredExport\Application\Common\Selection\PriceCollection\PriceCollectionCurrencyLabelSelection;
use Akeneo\Platform\TailoredExport\Application\Common\Selection\SelectionInterface;
use Akeneo\Platform\TailoredExport\Application\Common\SourceValue\Price;
use Akeneo\Platform\TailoredExport\Application\Common\SourceValue\PriceCollectionValue;
use Akeneo\Platform\TailoredExport\Application\Common\SourceValue\SourceValueInterface;
use Akeneo\Platform\TailoredExport\Application\MapValues\SelectionApplier\SelectionApplierInterface;
use Akeneo\Platform\TailoredExport\Domain\Query\FindCurrencyLabelsInterface;

class PriceCollectionCurrencyLabelSelectionApplier implements SelectionApplierInterface
{
    private FindCurrencyLabelsInterface $findCurrencyLabels;

    public function __construct(FindCurrencyLabelsInterface $findCurrencyLabels)
    {
        $this->findCurrencyLabels = $findCurrencyLabels;
    }

    public function applySelection(SelectionInterface $selection, SourceValueInterface $value): string
    {
        if (!($selection instanceof PriceCollectionCurrencyLabelSelection
            && $value instanceof PriceCollectionValue)) {
            throw new \InvalidArgumentException('Cannot apply Price collection selection on this entity');
        }

        $priceCollection = $value->getPriceCollection();
        $currencyCodes = array_map(static fn (Price $price) => $price->getCurrency(), $priceCollection);

        $currencyLabels = $this->findCurrencyLabels->byCodes($currencyCodes, $selection->getLocale());

        $selectedData = array_map(static function ($currencyCode) use ($currencyLabels) {
            return $currencyLabels[$currencyCode] ?? sprintf('[%s]', $currencyCode);
        }, $currencyCodes);

        return implode($selection->getSeparator(), $selectedData);
    }

    public function supports(SelectionInterface $selection, SourceValueInterface $value): bool
    {
        return $selection instanceof PriceCollectionCurrencyLabelSelection
            && $value instanceof PriceCollectionValue;
    }
}
