<?php

namespace Akeneo\Pim\Enrichment\Bundle\EventSubscriber\Db;

use Akeneo\Pim\Enrichment\Bundle\Command\ZddMigrations\ZddMigration;
use Akeneo\Platform\Bundle\InstallerBundle\Event\InstallerEvents;
use Doctrine\DBAL\Connection;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Webmozart\Assert\Assert;

class MarkZddMigrationsAsMigratedSubscriber implements EventSubscriberInterface
{
    /** @var ZddMigration[] */
    private array $zddMigrations;

    public function __construct(
        private Connection $connection,
        \Traversable $zddMigrations
    ) {
        $this->zddMigrations = iterator_to_array($zddMigrations);

        Assert::allIsInstanceOf($this->zddMigrations, ZddMigration::class);
        usort($this->zddMigrations, fn ($a, $b) => \strcmp(
            (new \ReflectionClass($a))->getShortName(),
            (new \ReflectionClass($b))->getShortName()
        ));
    }

    public static function getSubscribedEvents(): array
    {
        return [
            InstallerEvents::POST_DB_CREATE => 'markMigrations'
        ];
    }

    public function markMigrations(): void
    {
        foreach ($this->zddMigrations as $zddMigration) {
            $this->connection->executeQuery(<<<SQL
            INSERT INTO `pim_one_time_task` (`code`, `status`, `start_time`, `values`) 
            VALUES (:code, :status, NOW(), :values)
            ON DUPLICATE KEY UPDATE status=NEW.status, start_time=NOW();
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
