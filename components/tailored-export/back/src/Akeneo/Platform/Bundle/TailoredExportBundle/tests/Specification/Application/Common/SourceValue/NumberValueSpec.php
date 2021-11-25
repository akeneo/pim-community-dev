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

use Akeneo\Platform\TailoredExport\Application\Common\SourceValue\NumberValue;
use PhpSpec\ObjectBehavior;

class NumberValueSpec extends ObjectBehavior
{
    public function let()
    {
        $this->beConstructedWith('12.3');
    }

    public function it_is_initializable(): void
    {
        $this->shouldBeAnInstanceOf(NumberValue::class);
    }

    public function it_throws_an_exception_if_data_is_not_a_valid_number()
    {
        $this->beConstructedWith('a_string');
        $this->shouldThrow(\InvalidArgumentException::class)->duringInstantiation();
    }

    public function it_returns_the_data()
    {
        $this->getData()->shouldReturn('12.3');
    }
}
