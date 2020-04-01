<?php

declare(strict_types=1);

namespace spec\Akeneo\Connectivity\Connection\Application\Audit\Query;

use Akeneo\Connectivity\Connection\Application\Audit\Query\CountDailyEventsByConnectionQuery;
use Akeneo\Connectivity\Connection\Domain\Audit\Model\EventTypes;
use PhpSpec\ObjectBehavior;

/**
 * @author Romain Monceau <romain@akeneo.com>
 * @copyright 2019 Akeneo SAS (http://www.akeneo.com)
 * @license http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 */
class CountDailyEventsByConnectionQuerySpec extends ObjectBehavior
{
    public function let(): void
    {
        $this->beConstructedWith(
            EventTypes::PRODUCT_CREATED,
            new \DateTimeImmutable('2020-01-01 00:00:00', new \DateTimeZone('UTC')),
            new \DateTimeImmutable('2020-01-02 00:00:00', new \DateTimeZone('UTC'))
        );
    }

    public function it_is_initializable(): void
    {
        $this->shouldBeAnInstanceOf(CountDailyEventsByConnectionQuery::class);
    }

    public function it_returns_the_event_type(): void
    {
        $this->eventType()->shouldReturn(EventTypes::PRODUCT_CREATED);
    }

    public function it_returns_the_from_date_time(): void
    {
        $this->fromDateTime()->shouldBeLike(new \DateTimeImmutable('2020-01-01 00:00:00', new \DateTimeZone('UTC')));
    }

    public function it_returns_the_up_to_date_time(): void
    {
        $this->upToDateTime()->shouldBeLike(new \DateTimeImmutable('2020-01-02 00:00:00', new \DateTimeZone('UTC')));
    }

    public function it_checks_that_the_from_date_time_is_utc(): void
    {
        $this->beConstructedWith(
            EventTypes::PRODUCT_CREATED,
            new \DateTimeImmutable('2020-01-01 00:00:00', new \DateTimeZone('Europe/Paris')),
            new \DateTimeImmutable('2020-01-02 00:00:00', new \DateTimeZone('UTC'))
        );
        $this->shouldThrow(\InvalidArgumentException::class)->duringInstantiation();
    }

    public function it_checks_that_the_up_to_date_time_is_utc(): void
    {
        $this->beConstructedWith(
            EventTypes::PRODUCT_CREATED,
            new \DateTimeImmutable('2020-01-01 00:00:00', new \DateTimeZone('UTC')),
            new \DateTimeImmutable('2020-01-02 00:00:00', new \DateTimeZone('Europe/Paris'))
        );
        $this->shouldThrow(\InvalidArgumentException::class)->duringInstantiation();
    }
}
