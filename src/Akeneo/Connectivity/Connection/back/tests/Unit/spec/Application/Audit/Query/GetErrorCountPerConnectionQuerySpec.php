<?php

declare(strict_types=1);

namespace spec\Akeneo\Connectivity\Connection\Application\Audit\Query;

use Akeneo\Connectivity\Connection\Application\Audit\Query\GetErrorCountPerConnectionQuery;
use Akeneo\Connectivity\Connection\Domain\ErrorManagement\ErrorTypes;
use PhpSpec\ObjectBehavior;

/**
 * @author Pierre Jolly <pierre.jolly@akeneo.com>
 * @copyright 2020 Akeneo SAS (http://www.akeneo.com)
 * @license http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 */
class GetErrorCountPerConnectionQuerySpec extends ObjectBehavior
{
    public function let(): void
    {
        $this->beConstructedWith(
            ErrorTypes::BUSINESS,
            new \DateTimeImmutable('2020-05-10 00:00:00', new \DateTimeZone('UTC')),
            new \DateTimeImmutable('2020-05-12 00:00:00', new \DateTimeZone('UTC'))
        );
    }

    public function it_is_initializable(): void
    {
        $this->shouldBeAnInstanceOf(GetErrorCountPerConnectionQuery::class);
    }

    public function it_returns_the_error_type(): void
    {
        $this->errorType()->shouldReturn(ErrorTypes::BUSINESS);
    }

    public function it_returns_the_from_date_time(): void
    {
        $this->fromDateTime()->shouldBeLike(new \DateTimeImmutable('2020-05-10 00:00:00', new \DateTimeZone('UTC')));
    }

    public function it_returns_the_up_to_date_time(): void
    {
        $this->upToDateTime()->shouldBeLike(new \DateTimeImmutable('2020-05-12 00:00:00', new \DateTimeZone('UTC')));
    }

    public function it_checks_that_the_from_date_time_is_utc(): void
    {
        $this->beConstructedWith(
            ErrorTypes::TECHNICAL,
            new \DateTimeImmutable('2020-05-01 00:00:00', new \DateTimeZone('Europe/Paris')),
            new \DateTimeImmutable('2020-05-02 00:00:00', new \DateTimeZone('UTC'))
        );
        $this->shouldThrow(\InvalidArgumentException::class)->duringInstantiation();
    }

    public function it_checks_that_the_up_to_date_time_is_utc(): void
    {
        $this->beConstructedWith(
            ErrorTypes::TECHNICAL,
            new \DateTimeImmutable('2020-05-01 00:00:00', new \DateTimeZone('UTC')),
            new \DateTimeImmutable('2020-01-02 00:00:00', new \DateTimeZone('Europe/Paris'))
        );
        $this->shouldThrow(\InvalidArgumentException::class)->duringInstantiation();
    }
}
