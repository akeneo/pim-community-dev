<?php

declare(strict_types=1);

namespace Akeneo\Connectivity\Connection\Application\Audit\Command;

use Akeneo\Connectivity\Connection\Domain\Common\HourlyInterval;

/**
 * @author Pierre Jolly <pierre.jolly@akeneo.com>
 * @copyright 2020 Akeneo SAS (http://www.akeneo.com)
 * @license http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 */
final class UpdateDataDestinationProductEventCountCommand
{
    /** @var string */
    private $connectionCode;

    /** @var HourlyInterval */
    private $hourlyInterval;

    /** @var int */
    private $productEventCount;

    public function __construct(
        string $connectionCode,
        HourlyInterval $hourlyInterval,
        int $productEventCount
    ) {
        $this->connectionCode = $connectionCode;
        $this->hourlyInterval = $hourlyInterval;
        $this->productEventCount = $productEventCount;
    }

    public function connectionCode(): string
    {
        return $this->connectionCode;
    }

    public function hourlyInterval(): HourlyInterval
    {
        return $this->hourlyInterval;
    }

    public function productEventCount(): int
    {
        return $this->productEventCount;
    }
}
