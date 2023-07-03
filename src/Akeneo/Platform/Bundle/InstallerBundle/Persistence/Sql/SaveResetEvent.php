<?php

declare(strict_types=1);

namespace Akeneo\Platform\Bundle\InstallerBundle\Persistence\Sql;

use Doctrine\DBAL\Connection;

/**
 * @copyright 2023 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
final class SaveResetEvent
{
    public function __construct(
        private readonly Connection $connection,
        private readonly GetResetData $getResetData,
    ) {
    }

    public function withDatetime(\DateTimeImmutable $dateTime): void
    {
        $previousEvents = $this->getPreviousResetEvents();

        $newResetData = [
            'reset_events' => [
                ...$previousEvents,
                ['time' => $dateTime->format('c')],
            ],
        ];

        $this->connection->executeStatement(
            <<<SQL
REPLACE INTO `pim_configuration` (`code`, `values`)
VALUES ('reset_data', :reset_data);
SQL,
            ['reset_data' => \json_encode($newResetData)],
        );
    }

    private function getPreviousResetEvents(): array
    {
        $resetData = $this->getResetData->__invoke();

        return null === $resetData ? [] : $resetData['reset_events'];
    }
}
