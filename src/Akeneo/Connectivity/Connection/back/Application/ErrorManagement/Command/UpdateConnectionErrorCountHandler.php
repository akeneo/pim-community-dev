<?php
declare(strict_types=1);

namespace Akeneo\Connectivity\Connection\Application\ErrorManagement\Command;

use Akeneo\Connectivity\Connection\Domain\ErrorManagement\Persistence\Repository\ErrorCountRepositoryInterface;

/**
 * @author    Willy Mesnage <willy.mesnage@akeneo.com>
 * @copyright 2020 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class UpdateConnectionErrorCountHandler
{
    public function __construct(private ErrorCountRepositoryInterface $errorCountRepository)
    {
    }

    public function handle(UpdateConnectionErrorCountCommand $command): void
    {
        foreach ($command->errorCounts() as $hourlyErrorCount) {
            if (0 < $hourlyErrorCount->errorCount()) {
                $this->errorCountRepository->upsert($hourlyErrorCount);
            }
        }
    }
}
