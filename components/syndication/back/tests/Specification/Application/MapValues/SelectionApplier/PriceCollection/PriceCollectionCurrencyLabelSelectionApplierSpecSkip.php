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

namespace Specification\Akeneo\Platform\Syndication\Application\MapValues\SelectionApplier\PriceCollection;

use Akeneo\Platform\Syndication\Application\Common\Selection\Boolean\BooleanSelection;
use Akeneo\Platform\Syndication\Application\Common\Selection\PriceCollection\PriceCollectionCurrencyLabelSelection;
use Akeneo\Platform\Syndication\Application\Common\SourceValue\BooleanValue;
use Akeneo\Platform\Syndication\Application\Common\SourceValue\Price;
use Akeneo\Platform\Syndication\Application\Common\SourceValue\PriceCollectionValue;
use Akeneo\Platform\Syndication\Application\MapValues\SelectionApplier\PriceCollection\PriceCollectionCurrencyLabelSelectionApplier;
use Akeneo\Platform\Syndication\Domain\Query\FindCurrencyLabelsInterface;
use PhpSpec\ObjectBehavior;

class PriceCollectionCurrencyLabelSelectionApplierSpec extends ObjectBehavior
{
    public function let(FindCurrencyLabelsInterface $findCurrencyLabels)
    {
        $this->beConstructedWith($findCurrencyLabels);
    }

    public function it_is_initializable()
    {
        $this->shouldHaveType(PriceCollectionCurrencyLabelSelectionApplier::class);
    }

    public function it_applies_the_selection(FindCurrencyLabelsInterface $findCurrencyLabels)
    {
        $selection = new PriceCollectionCurrencyLabelSelection('|', 'fr_FR', ['USD', 'DKK']);
        $value = new PriceCollectionValue([new Price('102', 'EUR'), new Price('103', 'USD'), new Price('104', 'DKK')]);
        $findCurrencyLabels->byCodes(['USD', 'DKK'], 'fr_FR')->willReturn([
            'USD' => 'Dollars $',
            'DKK' => 'Donkey kong 🐒'
        ]);

        $this->applySelection($selection, $value)->shouldReturn('Dollars $|Donkey kong 🐒');
    }

    public function it_applies_the_selection_with_all_currencies(FindCurrencyLabelsInterface $findCurrencyLabels)
    {
        $selection = new PriceCollectionCurrencyLabelSelection('|', 'fr_FR', []);
        $value = new PriceCollectionValue([new Price('102', 'EUR'), new Price('103', 'USD'), new Price('104', 'DKK')]);
        $findCurrencyLabels->byCodes(['EUR', 'USD', 'DKK'], 'fr_FR')->willReturn([
            'EUR' => 'Euros €',
            'USD' => 'Dollars $',
            'DKK' => 'Donkey kong 🐒'
        ]);

        $this->applySelection($selection, $value)->shouldReturn('Euros €|Dollars $|Donkey kong 🐒');
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
        $selection = new PriceCollectionCurrencyLabelSelection('/', 'fr_FR', []);
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
