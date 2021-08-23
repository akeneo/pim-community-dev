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

namespace Akeneo\Platform\TailoredExport\Infrastructure\Query\Structure;

use Akeneo\Platform\TailoredExport\Domain\Query\FindCurrencyLabelsInterface;
use Akeneo\Tool\Component\Localization\CurrencyTranslatorInterface;

class FindCurrencyLabels implements FindCurrencyLabelsInterface
{
    private CurrencyTranslatorInterface $currencyTranslator;

    public function __construct(CurrencyTranslatorInterface $currencyTranslator)
    {
        $this->currencyTranslator = $currencyTranslator;
    }

    /**
     * @inheritDoc
     */
    public function byCodes(array $currencyCodes, string $locale): array
    {
        return array_reduce($currencyCodes, function (array $accumulator, string $currencyCode) use ($locale) {
            $currencyTranslation = $this->currencyTranslator->translate($currencyCode, $locale, '');
            $accumulator[$currencyCode] = $currencyTranslation === '' ? null : $currencyTranslation;

            return $accumulator;
        }, []);
    }
}
