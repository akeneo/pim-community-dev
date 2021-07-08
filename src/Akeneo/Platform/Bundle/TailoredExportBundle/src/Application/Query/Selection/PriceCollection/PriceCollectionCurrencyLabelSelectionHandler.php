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

namespace Akeneo\Platform\TailoredExport\Application\Query\Selection\PriceCollection;

use Akeneo\Platform\TailoredExport\Application\Query\Selection\SelectionHandlerInterface;
use Akeneo\Platform\TailoredExport\Application\Query\Selection\SelectionInterface;
use Akeneo\Platform\TailoredExport\Domain\SourceValue\Price;
use Akeneo\Platform\TailoredExport\Domain\SourceValue\PriceCollectionValue;
use Akeneo\Platform\TailoredExport\Domain\SourceValueInterface;
use Akeneo\Tool\Component\Localization\CurrencyTranslatorInterface;

class PriceCollectionCurrencyLabelSelectionHandler implements SelectionHandlerInterface
{
    private CurrencyTranslatorInterface $currencyTranslator;

    public function __construct(CurrencyTranslatorInterface $currencyTranslator)
    {
        $this->currencyTranslator = $currencyTranslator;
    }
    public function applySelection(SelectionInterface $selection, SourceValueInterface $value): string
    {
        if (!$this->supports($selection, $value)) {
            throw new \InvalidArgumentException('Cannot apply Price collection selection on this entity');
        }

        $priceCollection = $value->getPriceCollection();
        $selectedData = array_map(
            fn (Price $price) => $this->currencyTranslator->translate(
                $price->getCurrency(),
                $selection->getLocaleCode(),
                sprintf('[%s]', $price->getCurrency())
            ),
            $priceCollection
        );

        return implode($selection->getSeparator(), $selectedData);
    }

    public function supports(SelectionInterface $selection, SourceValueInterface $value): bool
    {
        return $selection instanceof PriceCollectionCurrencyLabelSelection
            && $value instanceof PriceCollectionValue;
    }
}
