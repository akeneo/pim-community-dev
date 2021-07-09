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

use Akeneo\Platform\TailoredExport\Application\Query\Selection\PriceCollection\PriceCollectionCurrencyLabelSelection;
use PhpSpec\ObjectBehavior;

class PriceCollectionCurrencyLabelSelectionSpec extends ObjectBehavior
{
    public function let()
    {
        $this->beConstructedWith('-', 'fr_FR');
    }

    function it_is_initializable()
    {
        $this->shouldHaveType(PriceCollectionCurrencyLabelSelection::class);
    }

    public function it_returns_the_separator()
    {
        $this->getSeparator()->shouldReturn('-');
    }

    public function it_returns_the_locale_code()
    {
        $this->getLocaleCode()->shouldReturn('fr_FR');
    }
}
