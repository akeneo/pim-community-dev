<?php

declare(strict_types=1);

namespace Akeneo\Connectivity\Connection\back\tests\Integration\Fixtures;

use Akeneo\Connectivity\Connection\Domain\ErrorManagement\Model\Write\HourlyErrorCount;
use Akeneo\Connectivity\Connection\Domain\ErrorManagement\Persistence\Repository\ErrorCountRepository;

/**
 * @author Pierre Jolly <pierre.jolly@akeneo.com>
 * @copyright 2019 Akeneo SAS (http://www.akeneo.com)
 * @license http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 */
class AuditErrorLoader
{
    /** @var ErrorCountRepository */
    private $errorCountRepository;

    public function __construct(ErrorCountRepository $errorCountRepository)
    {
        $this->errorCountRepository = $errorCountRepository;
    }

    public function insert(HourlyErrorCount $hourlyErrorCount): void
    {
        $this->errorCountRepository->upsert($hourlyErrorCount);
    }
}
