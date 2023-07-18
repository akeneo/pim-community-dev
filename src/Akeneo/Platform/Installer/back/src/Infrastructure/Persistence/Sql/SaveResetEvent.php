<?php

declare(strict_types=1);

namespace Akeneo\Platform\Installer\Infrastructure\Persistence\Sql;

use Doctrine\DBAL\Connection;

/**
 * @copyright 2023 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
final class SaveResetEvent
{
    public function __construct(
        private readonly Connection $connection,
        private readonly GetResetEvents $getResetEvents,
    ) {
    }

    public function withDatetime(\DateTimeImmutable $dateTime): void
    {
        $previousEvents = ($this->getResetEvents)();
        $newResetEvents = [...$previousEvents, ['time' => $dateTime]];

        $normalizedEvents = array_map(
            static fn (array $resetEvent) => ['time' => $resetEvent['time']->format('c')],
            $newResetEvents,
        );

        $this->connection->executeStatement(
            <<<SQL
REPLACE INTO `pim_configuration` (`code`, `values`)
VALUES ('reset_events', :reset_events);
SQL,
            ['reset_events' => \json_encode($normalizedEvents)],
        );
    }
}
