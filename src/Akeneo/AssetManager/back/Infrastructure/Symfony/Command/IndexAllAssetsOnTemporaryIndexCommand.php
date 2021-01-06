<?php

declare(strict_types=1);

namespace Akeneo\AssetManager\Infrastructure\Symfony\Command;

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2020 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

use Akeneo\AssetManager\Domain\Repository\AssetFamilyRepositoryInterface;
use Akeneo\AssetManager\Domain\Repository\AssetIndexerInterface;
use Akeneo\Tool\Bundle\ElasticsearchBundle\Client;
use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Types\Type;
use Doctrine\DBAL\Types\Types;
use Elasticsearch\Client as NativeClient;
use Elasticsearch\ClientBuilder;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Webmozart\Assert\Assert;

/**
 * This command indexes all assets on temporary index (services injected by DI).
 * This command is designed to be executed once by a cron in SAAS environment (not a perfect solution but the
 * simplest one within the actual stack). This command cannot be executed several times because:
 *  - concurrent cron jobs are forbidden by configuration (the same job cannot be executed in parallel)
 *  - we update a config in DB at the end of the command, this way we know if the command was already executed
 *
 * @author    Nicolas Marniesse <nicolas.marniesse@akeneo.com>
 * @copyright 2020 Akeneo SAS (http://www.akeneo.com)
 */
final class IndexAllAssetsOnTemporaryIndexCommand extends Command
{
    // if you copy/reuse this class please change the uuid
    public const CONFIGURATION_CODE = 'reindex_assets_eb7f3b50-98d0-43f5-bde9-edc505241e6c';
    private const REFRESH_INTERVAL_DURING_INDEXATION = '30s';

    protected static $defaultName = 'akeneo:asset-manager:index-all-assets-on-temporary-index';

    private Connection $connection;
    private AssetFamilyRepositoryInterface $assetFamilyRepository;
    private AssetIndexerInterface $assetIndexer;
    private Client $temporaryClient;
    private NativeClient $nativeClient;

    public function __construct(
        Connection $connection,
        AssetFamilyRepositoryInterface $assetFamilyRepository,
        AssetIndexerInterface $assetIndexer,
        Client $temporaryClient,
        ClientBuilder $clientBuilder,
        string $hosts
    ) {
        parent::__construct(self::$defaultName);

        $this->connection = $connection;
        $this->assetFamilyRepository = $assetFamilyRepository;
        $this->assetIndexer = $assetIndexer;
        $this->temporaryClient = $temporaryClient;
        $this->nativeClient = $clientBuilder->setHosts([$hosts])->build();
    }

    public function execute(InputInterface $input, OutputInterface $output)
    {
        $io = new SymfonyStyle($input, $output);

        if ($this->migrationIsAlreadyDone()) {
            $output->writeln("<info>The migration is already done. Nothing to do.</info>");

            return 0;
        }

        if (!$io->confirm('Are you sure to continue?', true)) {
            $output->writeln("<info>You decided to abort your Elasticearch mapping update</info>");

            return 0;
        }

        // Change refresh_interval to improve performance
        $oldRefreshInterval = $this->getRefreshIntervalForTemporaryIndex();
        $this->setRefreshIntervalToTemporaryIndex(self::REFRESH_INTERVAL_DURING_INDEXATION);

        $this->indexAllAssets($output);

        $this->setRefreshIntervalToTemporaryIndex($oldRefreshInterval);
        $this->markTheMigrationAsDone();

        $output->writeln('<info>Done</info>');

        return 0;
    }

    private function indexAllAssets(OutputInterface $output): void
    {
        $allAssetFamilies = $this->assetFamilyRepository->all();
        foreach ($allAssetFamilies as $assetFamily) {
            $output->writeln(sprintf(
                '<info>Re-index for the "%s" asset family...</info>',
                $assetFamily->getIdentifier()->normalize()
            ));
            $this->assetIndexer->indexByAssetFamily($assetFamily->getIdentifier());
        }
    }

    private function setRefreshIntervalToTemporaryIndex(?string $value): void
    {
        $indices = $this->nativeClient->indices();
        $aliases = $indices->getAlias(['name' => $this->temporaryClient->getIndexName()]);
        $newIndexName = array_keys($aliases)[0];

        $result = $this->nativeClient->indices()->putSettings([
            'index' => $newIndexName,
            'body' => [
                'index' => [
                    'refresh_interval' => $value,
                ],
            ],
        ]);

        Assert::true($result['acknowledged'], 'The refresh interval is not set.');
        Assert::same(
            $value,
            $this->getRefreshIntervalForTemporaryIndex(),
            'The refresh interval is not set.'
        );
    }

    private function getRefreshIntervalForTemporaryIndex(): ?string
    {
        $indices = $this->nativeClient->indices();
        $aliases = $indices->getAlias(['name' => $this->temporaryClient->getIndexName()]);
        $newIndexName = array_keys($aliases)[0];

        $results = $this->nativeClient->indices()->getSettings([
            'index' => $newIndexName,
        ]);

        return $results[$newIndexName]['settings']['index']['refresh_interval'] ?? null;
    }

    private function migrationIsAlreadyDone(): bool
    {
        $sql = <<<SQL
            SELECT EXISTS(
                SELECT 1 FROM pim_configuration WHERE code = :code
            ) as is_existing
            SQL;
        $statement = $this->connection->executeQuery($sql, ['code' => self::CONFIGURATION_CODE]);

        $platform = $this->connection->getDatabasePlatform();
        $result = $statement->fetch(\PDO::FETCH_ASSOC);
        $statement->closeCursor();

        return !Type::getType(Types::BOOLEAN)->convertToPhpValue($result['is_existing'], $platform);
    }

    private function markTheMigrationAsDone(): void
    {
        $sql = 'DELETE FROM pim_configuration WHERE code = :code';

        $this->connection->executeQuery($sql, ['code' => self::CONFIGURATION_CODE]);
    }
}
