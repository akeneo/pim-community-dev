<?php
declare(strict_types=1);

namespace Akeneo\Connectivity\Connection\Tests\CatalogBuilder;

use Akeneo\Connectivity\Connection\Application\ErrorManagement\Command\UpdateConnectionErrorCountCommand;
use Akeneo\Connectivity\Connection\Application\ErrorManagement\Command\UpdateConnectionErrorCountHandler;
use Akeneo\Connectivity\Connection\Domain\ErrorManagement\Model\Write\HourlyErrorCount;
use Akeneo\Connectivity\Connection\Domain\ValueObject\HourlyInterval;

/**
 * @author    Willy Mesnage <willy.mesnage@akeneo.com>
 * @copyright 2020 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class AuditErrorLoader
{
    /** @var UpdateConnectionErrorCountHandler */
    private $updateConnectionErrorCountHandler;

    public function __construct(UpdateConnectionErrorCountHandler $updateConnectionErrorCountHandler)
    {
        $this->updateConnectionErrorCountHandler = $updateConnectionErrorCountHandler;
    }

    public function insert(
        string $connectionCode,
        HourlyInterval $hourlyInterval,
        int $errorCount,
        string $errorType
    ): void {
        $hourlyErrorCount = new HourlyErrorCount(
            $connectionCode,
            $hourlyInterval,
            $errorCount,
            $errorType
        );
        $command = new UpdateConnectionErrorCountCommand([$hourlyErrorCount]);
        $this->updateConnectionErrorCountHandler->handle($command);
    }
}
