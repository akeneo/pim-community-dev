<?php

declare(strict_types=1);

namespace Akeneo\Connectivity\Connection\Domain\ErrorManagement\Model\Write;

use Akeneo\Connectivity\Connection\Domain\ErrorManagement\ErrorTypes;
use Akeneo\Connectivity\Connection\Domain\ErrorManagement\Model\ValueObject\ErrorType;
use Akeneo\Connectivity\Connection\Domain\Settings\Model\ValueObject\ConnectionCode;
use Akeneo\Connectivity\Connection\Domain\ValueObject\HourlyInterval;

/**
 * @author    Willy Mesnage <willy.mesnage@akeneo.com>
 * @copyright 2020 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class HourlyErrorCount
{
    private ConnectionCode $connectionCode;

    private int $errorCount;

    private ErrorType $errorType;

    public function __construct(
        string $connectionCode,
        private HourlyInterval $hourlyInterval,
        int $errorCount,
        string $errorType
    ) {
        $this->connectionCode = new ConnectionCode($connectionCode);
        if (0 > $errorCount) {
            throw new \InvalidArgumentException(
                \sprintf('The error count must be positive. Negative number "%s" given.', $errorCount)
            );
        }
        $this->errorCount = $errorCount;
        $this->errorType = new ErrorType($errorType);
    }

    public function connectionCode(): ConnectionCode
    {
        return $this->connectionCode;
    }

    public function hourlyInterval(): HourlyInterval
    {
        return $this->hourlyInterval;
    }

    public function errorCount(): int
    {
        return $this->errorCount;
    }

    public function errorType(): ErrorType
    {
        return $this->errorType;
    }
}
