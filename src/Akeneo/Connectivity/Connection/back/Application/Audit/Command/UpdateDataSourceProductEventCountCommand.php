<?php

declare(strict_types=1);

namespace Akeneo\Connectivity\Connection\Application\Audit\Command;

use Akeneo\Connectivity\Connection\Domain\Audit\Model\HourlyInterval;

/**
 * @author Romain Monceau <romain@akeneo.com>
 * @copyright 2019 Akeneo SAS (http://www.akeneo.com)
 * @license http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 */
final class UpdateDataSourceProductEventCountCommand
{
    /** @var HourlyInterval */
    private $hourlyInterval;

    public function __construct(HourlyInterval $hourlyInterval)
    {
        $this->hourlyInterval = $hourlyInterval;
    }

    public function hourlyInterval(): HourlyInterval
    {
        return $this->hourlyInterval;
    }
}
