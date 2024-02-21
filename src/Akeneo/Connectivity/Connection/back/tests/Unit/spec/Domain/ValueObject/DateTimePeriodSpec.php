<?php

declare(strict_types=1);

namespace spec\Akeneo\Connectivity\Connection\Domain\ValueObject;

use Akeneo\Connectivity\Connection\Domain\ValueObject\DateTimePeriod;
use PhpSpec\ObjectBehavior;

/**
 * @copyright 2020 Akeneo SAS (http://www.akeneo.com)
 * @license http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 */
class DateTimePeriodSpec extends ObjectBehavior
{
    public function let(): void
    {
        $this->beConstructedWith(
            new \DateTimeImmutable('2020-01-01 00:00:00', new \DateTimeZone('UTC')),
            new \DateTimeImmutable('2020-01-02 00:00:00', new \DateTimeZone('UTC')),
        );
    }

    public function it_is_initializable(): void
    {
        $this->shouldHaveType(DateTimePeriod::class);
    }

    public function it_returns_start_datetime(): void
    {
        $this->start()->shouldBeLike(
            new \DateTimeImmutable('2020-01-01 00:00:00', new \DateTimeZone('UTC'))
        );
    }

    public function it_returns_end_datetime(): void
    {
        $this->end()->shouldBeLike(
            new \DateTimeImmutable('2020-01-02 00:00:00', new \DateTimeZone('UTC'))
        );
    }

    public function it_throws_when_the_start_datetime_timezone_is_not_utc(): void
    {
        $this->beConstructedWith(
            new \DateTimeImmutable('2020-01-01 00:00:00', new \DateTimeZone('Europe/Paris')),
            new \DateTimeImmutable('2020-01-02 00:00:00', new \DateTimeZone('UTC')),
        );

        $this->shouldThrow(\InvalidArgumentException::class)->duringInstantiation();
    }

    public function it_throws_when_the_end_datetime_timezone_is_not_utc(): void
    {
        $this->beConstructedWith(
            new \DateTimeImmutable('2020-01-01 00:00:00', new \DateTimeZone('UTC')),
            new \DateTimeImmutable('2020-01-02 00:00:00', new \DateTimeZone('Europe/Paris')),
        );

        $this->shouldThrow(\InvalidArgumentException::class)->duringInstantiation();
    }
}
