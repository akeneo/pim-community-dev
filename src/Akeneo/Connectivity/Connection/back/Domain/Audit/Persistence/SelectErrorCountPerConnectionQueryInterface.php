<?php

declare(strict_types=1);

namespace Akeneo\Connectivity\Connection\Domain\Audit\Persistence;

use Akeneo\Connectivity\Connection\Domain\Audit\Model\Read\ErrorCountPerConnection;
use Akeneo\Connectivity\Connection\Domain\ErrorManagement\Model\ValueObject\ErrorType;

/**
 * @author Pierre Jolly <romain@akeneo.com>
 * @copyright 2020 Akeneo SAS (http://www.akeneo.com)
 * @license http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 */
interface SelectErrorCountPerConnectionQueryInterface
{
    public function execute(
        ErrorType $errorType,
        \DateTimeImmutable $fromDateTime,
        \DateTimeImmutable $upToDateTime
    ): ErrorCountPerConnection;
}
