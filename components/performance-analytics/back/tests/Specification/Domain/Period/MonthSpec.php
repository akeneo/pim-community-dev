<?php

declare(strict_types=1);

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2022 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Specification\Akeneo\PerformanceAnalytics\Domain\Period;

use Akeneo\PerformanceAnalytics\Domain\Period;
use PhpSpec\ObjectBehavior;

final class MonthSpec extends ObjectBehavior
{
    public function it_returns_a_formatted_month(): void
    {
        $this->beConstructedThrough('fromDate', [new \DateTimeImmutable('2022-09-27 12:09:54')]);
        $this->shouldImplement(Period::class);
        $this->toString()->shouldReturn('2022-09');
    }
}
