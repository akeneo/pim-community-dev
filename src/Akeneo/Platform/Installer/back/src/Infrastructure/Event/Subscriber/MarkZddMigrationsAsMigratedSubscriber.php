<?php

declare(strict_types=1);

namespace Akeneo\Platform\Installer\Infrastructure\Event\Subscriber;

use Akeneo\Platform\Installer\Infrastructure\Command\ZddMigration;
use Akeneo\Platform\Installer\Infrastructure\Event\InstallerEvents;
use Doctrine\DBAL\Connection;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Webmozart\Assert\Assert;

/**
 * When installing a fresh new database, this subscriber will automatically mark ZDD Migrations as "migrated".
 *
 * @see ZddMigration
 *
 * @copyright 2022 Akeneo SAS (https://www.akeneo.com)
 * @license https://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 */
class MarkZddMigrationsAsMigratedSubscriber implements EventSubscriberInterface
{
    /** @var ZddMigration[] */
    private array $zddMigrations;

    /**
     * @param Connection $connection
     * @param iterable<mixed> $zddMigrations
     */
    public function __construct(
        private Connection $connection,
        iterable $zddMigrations
    ) {
        Assert::allIsInstanceOf($zddMigrations, ZddMigration::class);
        $zddMigrations = $zddMigrations instanceof \Traversable ? \iterator_to_array($zddMigrations) : $zddMigrations;
        $this->zddMigrations = $zddMigrations;
    }

    public static function getSubscribedEvents(): array
    {
        return [
            InstallerEvents::POST_DB_CREATE => [
                ['markMigrations', 0],
            ]
        ];
    }

    public function markMigrations(): void
    {
        foreach ($this->zddMigrations as $zddMigration) {
            $this->connection->executeQuery(<<<SQL
            INSERT INTO `pim_one_time_task` (`code`, `status`, `start_time`, `values`) 
            VALUES (:code, :status, NOW(), :values)
            ON DUPLICATE KEY UPDATE status=VALUES(status), start_time=NOW();
        SQL, [
                'code' => $this->getZddMigrationCode($zddMigration),
                'status' => 'finished',
                'values' => \json_encode((object) []),
            ]);
        }
    }

    private function getZddMigrationCode(ZddMigration $zddMigration): string
    {
        return \sprintf('zdd_%s', $zddMigration->getName());
    }
}
