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

namespace Specification\Akeneo\Platform\TailoredExport\Application\Query\Selection\PriceCollection;

use Akeneo\Platform\TailoredExport\Application\Query\Selection\Boolean\BooleanSelection;
use Akeneo\Platform\TailoredExport\Application\Query\Selection\PriceCollection\PriceCollectionCurrencyLabelSelection;
use Akeneo\Platform\TailoredExport\Application\Query\Selection\PriceCollection\PriceCollectionCurrencyLabelSelectionHandler;
use Akeneo\Platform\TailoredExport\Domain\SourceValue\BooleanValue;
use Akeneo\Platform\TailoredExport\Domain\SourceValue\Price;
use Akeneo\Platform\TailoredExport\Domain\SourceValue\PriceCollectionValue;
use Akeneo\Tool\Component\Localization\CurrencyTranslatorInterface;
use PhpSpec\ObjectBehavior;

class PriceCollectionCurrencyLabelSelectionHandlerSpec extends ObjectBehavior
{
    public function let(CurrencyTranslatorInterface $currencyTranslator)
    {
        $this->beConstructedWith($currencyTranslator);
    }

    public function it_is_initializable()
    {
        $this->shouldHaveType(PriceCollectionCurrencyLabelSelectionHandler::class);
    }

    public function it_applies_the_selection($currencyTranslator)
    {
        $selection = new PriceCollectionCurrencyLabelSelection('|', 'fr_FR');
        $value = new PriceCollectionValue([new Price('102', 'EUR'), new Price('103', 'USD'), new Price('104', 'DKK')]);
        $currencyTranslator->translate('EUR', 'fr_FR', '[EUR]')->willReturn('Euros €');
        $currencyTranslator->translate('USD', 'fr_FR', '[USD]')->willReturn('Dollars $');
        $currencyTranslator->translate('DKK', 'fr_FR', '[DKK]')->willReturn('Donkey kong 🐒');

        $this->applySelection($selection, $value)
            ->shouldReturn('Euros €|Dollars $|Donkey kong 🐒');
    }

    public function it_does_not_apply_selection_on_not_supported_selections_and_values()
    {
        $notSupportedSelection = new BooleanSelection();
        $notSupportedValue = new BooleanValue(true);

        $this
            ->shouldThrow(new \InvalidArgumentException('Cannot apply Price collection selection on this entity'))
            ->during('applySelection', [$notSupportedSelection, $notSupportedValue]);
    }

    public function it_supports_price_collection_code_selection_with_price_collection_value()
    {
        $selection = new PriceCollectionCurrencyLabelSelection('/', 'fr_FR');
        $value = new PriceCollectionValue([]);

        $this->supports($selection, $value)->shouldReturn(true);
    }

    public function it_does_not_support_other_selections_and_values()
    {
        $notSupportedSelection = new BooleanSelection();
        $notSupportedValue = new BooleanValue(true);

        $this->supports($notSupportedSelection, $notSupportedValue)->shouldReturn(false);
    }
}
