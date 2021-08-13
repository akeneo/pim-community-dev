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

namespace Specification\Akeneo\Platform\TailoredExport\Application\Common\SourceValue;

use Akeneo\Platform\TailoredExport\Application\Common\SourceValue\Price;
use PhpSpec\ObjectBehavior;

class PriceSpec extends ObjectBehavior
{
    public function let()
    {
        $this->beConstructedWith('10', 'EUR');
    }

    public function it_is_initializable(): void
    {
        $this->shouldBeAnInstanceOf(Price::class);
    }

    public function it_returns_the_amount()
    {
        $this->getAmount()->shouldReturn('10');
    }

    public function it_returns_the_currency()
    {
        $this->getCurrency()->shouldReturn('EUR');
    }
}
