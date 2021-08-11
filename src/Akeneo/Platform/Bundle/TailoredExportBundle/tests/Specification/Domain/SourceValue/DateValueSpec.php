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

namespace Specification\Akeneo\Platform\TailoredExport\Domain\SourceValue;

use Akeneo\Platform\TailoredExport\Domain\SourceValue\DateValue;
use PhpSpec\ObjectBehavior;

class DateValueSpec extends ObjectBehavior
{
    public function it_is_initializable(): void
    {
        $this->beConstructedWith(new \DateTimeImmutable());
        $this->shouldBeAnInstanceOf(DateValue::class);
    }

    public function it_returns_the_date()
    {
        $date = new \DateTimeImmutable();
        $this->beConstructedWith($date);
        $this->getData()->shouldReturn($date);
    }
}
