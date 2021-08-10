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

namespace Specification\Akeneo\Platform\TailoredExport\Domain\Model\SourceValue;

use Akeneo\Platform\TailoredExport\Domain\Model\SourceValue\BooleanValue;
use PhpSpec\ObjectBehavior;

class BooleanValueSpec extends ObjectBehavior
{
    public function let()
    {
        $this->beConstructedWith(true);
    }

    public function it_is_initializable(): void
    {
        $this->shouldBeAnInstanceOf(BooleanValue::class);
    }

    public function it_returns_the_data()
    {
        $this->getData()->shouldReturn(true);
    }
}
