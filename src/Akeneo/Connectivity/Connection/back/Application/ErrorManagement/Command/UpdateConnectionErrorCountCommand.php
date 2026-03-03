<?php
declare(strict_types=1);

namespace Akeneo\Connectivity\Connection\Application\ErrorManagement\Command;

use Akeneo\Connectivity\Connection\Domain\ErrorManagement\Model\Write\HourlyErrorCount;

/**
 * @author    Willy Mesnage <willy.mesnage@akeneo.com>
 * @copyright 2020 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
final class UpdateConnectionErrorCountCommand
{
    /**
     * @param HourlyErrorCount[] $errorCounts
     */
    public function __construct(private array $errorCounts)
    {
    }

    /**
     * @return HourlyErrorCount[]
     */
    public function errorCounts(): array
    {
        return $this->errorCounts;
    }
}
