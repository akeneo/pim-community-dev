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

namespace Akeneo\Platform\TailoredExport\Application\Common\Selection\PriceCollection;

use Webmozart\Assert\Assert;

final class PriceCollectionCurrencyLabelSelection implements PriceCollectionSelectionInterface
{
    public const TYPE = 'currency_label';

    private string $separator;
    private string $locale;
    private array $currencies;

    public function __construct(string $separator, string $locale, array $currencies)
    {
        Assert::allString($currencies);

        $this->separator = $separator;
        $this->locale = $locale;
        $this->currencies = $currencies;
    }

    public function getSeparator(): string
    {
        return $this->separator;
    }

    public function getLocale(): string
    {
        return $this->locale;
    }

    public function getCurrencies(): array
    {
        return $this->currencies;
    }
}
