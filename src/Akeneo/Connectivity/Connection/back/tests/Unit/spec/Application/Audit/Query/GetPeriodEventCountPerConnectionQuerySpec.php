<?php

declare(strict_types=1);

namespace spec\Akeneo\Connectivity\Connection\Application\Audit\Query;

use Akeneo\Connectivity\Connection\Application\Audit\Query\GetPeriodEventCountPerConnectionQuery;
use Akeneo\Connectivity\Connection\Domain\Audit\Model\EventTypes;
use Akeneo\Connectivity\Connection\Domain\ValueObject\DateTimePeriod;
use PhpSpec\ObjectBehavior;

/**
 * @author Romain Monceau <romain@akeneo.com>
 * @copyright 2019 Akeneo SAS (http://www.akeneo.com)
 * @license http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 */
class GetPeriodEventCountPerConnectionQuerySpec extends ObjectBehavior
{
    public function let(): void
    {
        $this->beConstructedWith(
            EventTypes::PRODUCT_CREATED,
            new DateTimePeriod(
                new \DateTimeImmutable('2020-01-01 00:00:00', new \DateTimeZone('UTC')),
                new \DateTimeImmutable('2020-01-02 00:00:00', new \DateTimeZone('UTC'))
            )
        );
    }

    public function it_is_initializable(): void
    {
        $this->shouldBeAnInstanceOf(GetPeriodEventCountPerConnectionQuery::class);
    }

    public function it_returns_the_event_type(): void
    {
        $this->eventType()->shouldReturn(EventTypes::PRODUCT_CREATED);
    }

    public function it_returns_the_period(): void
    {
        $this->period()->shouldBeLike(
            new DateTimePeriod(
                new \DateTimeImmutable('2020-01-01 00:00:00', new \DateTimeZone('UTC')),
                new \DateTimeImmutable('2020-01-02 00:00:00', new \DateTimeZone('UTC'))
            )
        );
    }
}
