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

namespace Specification\Akeneo\Platform\TailoredExport\Infrastructure\Connector\Processor\PropertySelector;

use Akeneo\Pim\Enrichment\Component\Product\Model\ProductInterface;
use PhpSpec\ObjectBehavior;

class EnabledSelectorSpec extends ObjectBehavior
{
    public function it_returns_property_name_supported()
    {
        $this->supports([], 'enabled')->shouldReturn(true);
        $this->supports([], 'family')->shouldReturn(false);
    }

    public function it_selects_the_enabled_value(
        ProductInterface $enabledProduct,
        ProductInterface $disabledProduct
    ) {
        $enabledProduct->isEnabled()->willReturn(true);
        $disabledProduct->isEnabled()->willReturn(false);

        $this->applySelection([], $enabledProduct)->shouldReturn('1');
        $this->applySelection([], $disabledProduct)->shouldReturn('0');
    }
}
