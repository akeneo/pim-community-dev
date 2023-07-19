<?php

declare(strict_types=1);

namespace Akeneo\Platform\Installer\Infrastructure\Persistence\Sql;

use Doctrine\DBAL\Connection;

/**
 * @copyright 2023 Akeneo SAS (https://www.akeneo.com)
 * @license   https://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
final class GetResetEvents
{
    public function __construct(
        private readonly Connection $connection,
    ) {
    }

    /**
     * @return array<mixed>
     *
     * @throws \Doctrine\DBAL\Driver\Exception
     * @throws \Doctrine\DBAL\Exception
     */
    public function __invoke(): array
    {
        $sql = <<< SQL
            SELECT `values` FROM pim_configuration WHERE code = 'reset_events';
        SQL;

        $values = $this->connection->executeQuery($sql)->fetchOne();

        if (false === $values) {
            return [];
        }

        $normalizedResetEvents = \json_decode((string) $values, true, 512, JSON_THROW_ON_ERROR);

        return array_map(
            static fn (array $resetEvent): array => ['time' => new \DateTimeImmutable($resetEvent['time'])],
            $normalizedResetEvents,
        );
    }
}
