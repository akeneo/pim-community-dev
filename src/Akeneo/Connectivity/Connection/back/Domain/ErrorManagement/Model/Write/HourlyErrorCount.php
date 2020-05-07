<?php
declare(strict_types=1);

namespace Akeneo\Connectivity\Connection\Domain\ErrorManagement\Model\Write;

use Akeneo\Connectivity\Connection\Domain\Common\HourlyInterval;
use Akeneo\Connectivity\Connection\Domain\ErrorManagement\ErrorTypes;
use Akeneo\Connectivity\Connection\Domain\Settings\Model\ValueObject\ConnectionCode;

/**
 * @author    Willy Mesnage <willy.mesnage@akeneo.com>
 * @copyright 2020 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class HourlyErrorCount
{
    /** @var string */
    private $connectionCode;

    /** @var HourlyInterval */
    private $hourlyInterval;

    /** @var int */
    private $errorCount;

    /** @var string */
    private $errorType;

    public function __construct(
        string $connectionCode,
        HourlyInterval $hourlyInterval,
        int $errorCount,
        string $errorType
    ) {
        $this->connectionCode = new ConnectionCode($connectionCode);
        $this->hourlyInterval = $hourlyInterval;
        if (0 > $errorCount) {
            throw new \InvalidArgumentException(
                sprintf('The error count must be positive. Negative number "%s" given.', $errorCount)
            );
        }
        $this->errorCount = $errorCount;
        if (!in_array($errorType, ErrorTypes::getAll())) {
            throw new \InvalidArgumentException(
                sprintf('The given error type "%s" is unknown.', $errorType)
            );
        }
        $this->errorType = $errorType;
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

    public function errorType(): string
    {
        return $this->errorType;
    }
}
