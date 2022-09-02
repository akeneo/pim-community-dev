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

final class PriceCollectionAmountSelection implements PriceCollectionSelectionInterface
{
    public const TYPE = 'amount';

    public function __construct(
        private string $separator,
        private array $currencies,
    ) {
        Assert::allString($currencies);
    }

    public function getSeparator(): string
    {
        return $this->separator;
    }

    /**
     * @return string[]
     */
    public function getCurrencies(): array
    {
        return $this->currencies;
    }
}
