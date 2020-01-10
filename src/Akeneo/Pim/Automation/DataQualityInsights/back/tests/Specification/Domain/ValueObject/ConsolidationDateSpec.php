<?php

declare(strict_types=1);

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2019 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Specification\Akeneo\Pim\Automation\DataQualityInsights\Domain\ValueObject;

use Akeneo\Pim\Automation\DataQualityInsights\Domain\ValueObject\Periodicity;
use PhpSpec\ObjectBehavior;

final class ConsolidationDateSpec extends ObjectBehavior
{
    public function it_returns_false_if_it_is_not_the_last_day_of_the_week()
    {
        $this->beConstructedWith(new \DateTimeImmutable('Monday last week'));
        $this->isLastDayOfWeek()->shouldReturn(false);
    }

    public function it_returns_true_if_it_is_the_last_day_of_the_week()
    {
        $this->beConstructedWith(new \DateTimeImmutable('Sunday last week'));
        $this->isLastDayOfWeek()->shouldReturn(true);
    }

    public function it_returns_true_if_it_is_the_last_day_of_the_month()
    {
        $this->beConstructedWith(new \DateTimeImmutable('2019-12-31'));
        $this->isLastDayOfMonth()->shouldReturn(true);
    }

    public function it_returns_false_if_it_is_not_the_last_day_of_the_month()
    {
        $this->beConstructedWith(new \DateTimeImmutable('2019-12-30'));
        $this->isLastDayOfMonth()->shouldReturn(false);
    }

    public function it_formats_a_date()
    {
        $this->beConstructedWith(new \DateTimeImmutable('2019-12-09 12:43:37'));
        $this->format()->shouldReturn('2019-12-09');
    }

    public function it_modifies_a_date()
    {
        $this->beConstructedWith(new \DateTimeImmutable('2019-12-09 12:43:37'));

        $modifiedDate = $this->modify('-2 DAY');
        $modifiedDate->format()->shouldReturn('2019-12-07');
    }
}
