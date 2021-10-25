<?php

declare(strict_types=1);

namespace Akeneo\Platform\Job\Infrastructure\Query;

use Akeneo\Platform\Job\Domain\Query\CountJobQueryInterface;
use Doctrine\DBAL\Connection;

/**
 * @author Pierre Jolly <pierre.jolly@akeneo.com>
 * @copyright 2021 Akeneo SAS (http://www.akeneo.com)
 * @license http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 */
final class CountJobQuery implements CountJobQueryInterface
{
    private Connection $sqlConnection;

    public function __construct(Connection $sqlConnection)
    {
        $this->sqlConnection = $sqlConnection;
    }

    public function all(): int
    {
        return 10;
    }
}
