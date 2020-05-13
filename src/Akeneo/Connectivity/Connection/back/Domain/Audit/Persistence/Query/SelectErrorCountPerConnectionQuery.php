<?php

declare(strict_types=1);

namespace Akeneo\Connectivity\Connection\Domain\Audit\Persistence\Query;

use Akeneo\Connectivity\Connection\Domain\Audit\Model\Read\ErrorCountPerConnection;

/**
 * @author Pierre Jolly <romain@akeneo.com>
 * @copyright 2020 Akeneo SAS (http://www.akeneo.com)
 * @license http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 */
interface SelectErrorCountPerConnectionQuery
{
    /**
     * @return ErrorCountPerConnection
     */
    public function execute(
        string $eventType,
        \DateTimeImmutable $fromDateTime,
        \DateTimeImmutable $upToDateTime
    ): ErrorCountPerConnection;
}
