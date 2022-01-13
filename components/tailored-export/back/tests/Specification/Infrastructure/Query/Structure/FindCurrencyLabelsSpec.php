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

namespace Specification\Akeneo\Platform\TailoredExport\Infrastructure\Query\Structure;

use Akeneo\Platform\TailoredExport\Infrastructure\Query\Structure\FindCurrencyLabels;
use Akeneo\Tool\Component\Localization\CurrencyTranslatorInterface;
use PhpSpec\ObjectBehavior;

class FindCurrencyLabelsSpec extends ObjectBehavior
{
    public function let(
        CurrencyTranslatorInterface $currencyTranslator
    ): void {
        $this->beConstructedWith($currencyTranslator);
    }

    public function it_is_initializable(): void
    {
        $this->shouldHaveType(FindCurrencyLabels::class);
    }

    public function it_finds_the_labels_for_multiple_currencies(
        CurrencyTranslatorInterface $currencyTranslator
    ): void {
        $currencyTranslator->translate('EUR', 'fr_FR', '')->willReturn('Euros');
        $currencyTranslator->translate('DOLLAR', 'fr_FR', '')->willReturn('Dollars');

        $this->byCodes(['EUR', 'DOLLAR'], 'fr_FR')->shouldReturn(
            ['EUR' => 'Euros', 'DOLLAR' => 'Dollars']
        );
    }

    public function it_returns_null_if_no_label_for_any_currency(
        CurrencyTranslatorInterface $currencyTranslator
    ): void {
        $currencyTranslator->translate('EUR', 'fr_FR', '')->willReturn('');
        $currencyTranslator->translate('DOLLAR', 'fr_FR', '')->willReturn('');

        $this->byCodes(['EUR', 'DOLLAR'], 'fr_FR')->shouldReturn(
            ['EUR' => null, 'DOLLAR' => null]
        );
    }
}
