<?php

declare(strict_types=1);

namespace Akeneo\Connectivity\Connection\Application\Audit\Command;

use Akeneo\Connectivity\Connection\Domain\ValueObject\HourlyInterval;

/**
 * @author Romain Monceau <romain@akeneo.com>
 * @copyright 2019 Akeneo SAS (http://www.akeneo.com)
 * @license http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 */
final class UpdateDataSourceProductEventCountCommand
{
    public function __construct(private HourlyInterval $hourlyInterval)
    {
    }

    public function hourlyInterval(): HourlyInterval
    {
        return $this->hourlyInterval;
    }
}
