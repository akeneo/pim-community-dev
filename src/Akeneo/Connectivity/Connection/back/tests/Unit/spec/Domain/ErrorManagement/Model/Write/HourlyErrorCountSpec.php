<?php
declare(strict_types=1);

namespace spec\Akeneo\Connectivity\Connection\Domain\ErrorManagement\Model\Write;

use Akeneo\Connectivity\Connection\Domain\ErrorManagement\ErrorTypes;
use Akeneo\Connectivity\Connection\Domain\ErrorManagement\Model\ValueObject\ErrorType;
use Akeneo\Connectivity\Connection\Domain\ErrorManagement\Model\Write\HourlyErrorCount;
use Akeneo\Connectivity\Connection\Domain\Settings\Model\ValueObject\ConnectionCode;
use Akeneo\Connectivity\Connection\Domain\ValueObject\HourlyInterval;
use PhpSpec\ObjectBehavior;

class HourlyErrorCountSpec extends ObjectBehavior
{
    public function let(): void
    {
        $this->beConstructedWith(
            'magento',
            HourlyInterval::createFromDateTime(
                new \DateTimeImmutable('2020-01-01 10:00:00', new \DateTimeZone('UTC'))
            ),
            329,
            ErrorTypes::BUSINESS
        );
    }

    public function it_is_initializable(): void
    {
        $this->shouldHaveType(HourlyErrorCount::class);
    }

    public function it_returns_the_connection_code(): void
    {
        $connectionCode = $this->connectionCode();
        $connectionCode->shouldBeAnInstanceOf(ConnectionCode::class);
        $connectionCode->__toString()->shouldReturn('magento');
    }

    public function it_returns_the_hourly_interval(): void
    {
        $hourlyInterval = HourlyInterval::createFromDateTime(
            new \DateTimeImmutable('2020-01-01 10:00:00', new \DateTimeZone('UTC'))
        );
        $this->beConstructedWith(
            'magento',
            $hourlyInterval,
            329,
            ErrorTypes::BUSINESS
        );

        $this->hourlyInterval()->shouldBe($hourlyInterval);
    }

    public function it_returns_the_error_count(): void
    {
        $this->errorCount()->shouldBe(329);
    }

    public function it_returns_the_error_type(): void
    {
        $errorType = $this->errorType();
        $errorType->shouldBeAnInstanceOf(ErrorType::class);
        $errorType->__toString()->shouldReturn('business');
    }

    public function it_validates_that_the_count_is_positive(): void
    {
        $hourlyInterval = HourlyInterval::createFromDateTime(
            new \DateTimeImmutable('2020-01-01 10:00:00', new \DateTimeZone('UTC'))
        );
        $this->beConstructedWith('erp', $hourlyInterval, -5, ErrorTypes::BUSINESS);
        $this->shouldThrow(
            new \InvalidArgumentException('The error count must be positive. Negative number "-5" given.')
        )->duringInstantiation();
    }

    public function it_validates_the_error_type(): void
    {
        $hourlyInterval = HourlyInterval::createFromDateTime(
            new \DateTimeImmutable('2020-01-01 10:00:00', new \DateTimeZone('UTC'))
        );
        $this->beConstructedWith('erp', $hourlyInterval, 12, 'Error');
        $this->shouldThrow(
            new \InvalidArgumentException('The given error type "Error" is unknown.')
        )->duringInstantiation();
    }
}
