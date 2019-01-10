<?php

declare(strict_types=1);

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2018 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Specification\Akeneo\Pim\Automation\FranklinInsights\Infrastructure\Connector\Time;

use Akeneo\Pim\Automation\FranklinInsights\Infrastructure\Connector\Time\DateTimeBuilderInterface;
use PhpSpec\ObjectBehavior;

/**
 * @author Romain Monceau <romain@akeneo.com>
 */
class RealDateTimeBuilderSpec extends ObjectBehavior
{
    public function it_is_a_datetime_builder(): void
    {
        $this->shouldImplement(DateTimeBuilderInterface::class);
    }

    public function it_builds_a_datetime_for_the_first_product_fetch(): void
    {
        $this->buildForFirstProductFetch()->shouldReturnAnInstanceOf(\DateTime::class);
    }

    public function it_builds_a_datetime_from_a_date(): void
    {
        $this->fromString('2013-01-01')->shouldReturnAnInstanceOf(\DateTime::class);
    }

    public function it_cannot_build_a_datetime_from_an_incorrect_formatted_string(): void
    {
        $this->shouldThrow(\Exception::class)->during('fromString', ['toto']);
    }

    public function it_removes_on_hour_from_a_datetime(): void
    {
        $datetime = new \DateTime('2017-06-06 15:10:36');
        $expectedDateTime = new \DateTime('2017-06-06 14:10:36');

        $this->removeOneHour($datetime)->shouldBeLike($expectedDateTime);
    }
}
