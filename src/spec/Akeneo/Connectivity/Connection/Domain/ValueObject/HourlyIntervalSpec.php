<?php

declare(strict_types=1);

namespace spec\Akeneo\Connectivity\Connection\Domain\ValueObject;

use Akeneo\Connectivity\Connection\Domain\ValueObject\HourlyInterval;
use PhpSpec\ObjectBehavior;

/**
 * @copyright 2020 Akeneo SAS (http://www.akeneo.com)
 * @license http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 */
class HourlyIntervalSpec extends ObjectBehavior
{
    public function let(): void
    {
        $this->beConstructedThrough('createFromDateTime', [
            new \DateTimeImmutable('2020-01-01 10:00:00', new \DateTimeZone('UTC'))
        ]);
    }

    public function it_returns_from_datetime(): void
    {
        $this->beConstructedThrough('createFromDateTime', [
            new \DateTimeImmutable('2020-01-01 10:00:00', new \DateTimeZone('UTC'))
        ]);

        $this->fromDateTime()->shouldBeLike(
            new \DateTimeImmutable('2020-01-01 10:00:00', new \DateTimeZone('UTC'))
        );
    }

    public function it_returns_up_to_datetime(): void
    {
        $this->beConstructedThrough('createFromDateTime', [
            new \DateTimeImmutable('2020-01-01 10:00:00', new \DateTimeZone('UTC'))
        ]);

        $this->upToDateTime()->shouldBeLike(
            new \DateTimeImmutable('2020-01-01 11:00:00', new \DateTimeZone('UTC'))
        );
    }

    public function it_is_initializable(): void
    {
        $this->shouldHaveType(HourlyInterval::class);
    }

    public function it_throws_when_the_timezone_is_not_utc(): void
    {
        $this->beConstructedThrough('createFromDateTime', [
            new \DateTimeImmutable('2020-01-01 10:00:00', new \DateTimeZone('europe/paris'))
        ]);

        $this->shouldThrow('\InvalidArgumentException')->duringInstantiation();
    }

    public function it_compares_two_equals_hourly_intervals_created_with_the_same_hours(): void
    {
        $this->beConstructedThrough('createFromDateTime', [
            new \DateTimeImmutable('2020-01-01 10:00:00', new \DateTimeZone('UTC'))
        ]);

        $hourlyInterval = HourlyInterval::createFromDateTime(
            new \DateTimeImmutable('2020-01-01 10:00:00', new \DateTimeZone('UTC'))
        );

        $this->equals($hourlyInterval)->shouldReturn(true);
    }

    public function it_compares_two_equals_hourly_intervals_created_with_differents_hours(): void
    {
        $this->beConstructedThrough('createFromDateTime', [
            new \DateTimeImmutable('2020-01-01 10:00:00', new \DateTimeZone('UTC'))
        ]);

        $hourlyInterval = HourlyInterval::createFromDateTime(
            new \DateTimeImmutable('2020-01-01 10:59:59', new \DateTimeZone('UTC'))
        );

        $this->equals($hourlyInterval)->shouldReturn(true);
    }

    public function it_compares_two_differents_hourly_intervals(): void
    {
        $this->beConstructedThrough('createFromDateTime', [
            new \DateTimeImmutable('2020-01-01 10:00:00', new \DateTimeZone('UTC'))
        ]);

        $hourlyInterval = HourlyInterval::createFromDateTime(
            new \DateTimeImmutable('2020-01-01 11:00:00', new \DateTimeZone('UTC'))
        );

        $this->equals($hourlyInterval)->shouldReturn(false);
    }
}
