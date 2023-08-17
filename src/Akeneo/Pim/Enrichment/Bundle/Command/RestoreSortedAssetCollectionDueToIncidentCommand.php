<?php

namespace Akeneo\Pim\Enrichment\Bundle\Command;

use Akeneo\Pim\Enrichment\Component\Product\Repository\ProductModelRepositoryInterface;
use Akeneo\Pim\Permission\Bundle\Persistence\ORM\EntityWithValue\ProductRepository;
use Akeneo\Pim\Permission\Component\Updater\GrantedProductModelUpdater;
use Akeneo\Pim\Permission\Component\Updater\GrantedProductUpdater;
use Akeneo\Tool\Bundle\ConnectorBundle\Doctrine\UnitOfWorkAndRepositoriesClearer;
use Akeneo\Tool\Component\StorageUtils\Saver\BulkSaverInterface;
use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Types\Type;
use Doctrine\DBAL\Types\Types;
use Psr\Log\LoggerInterface;
use Ramsey\Uuid\Uuid;
use Ramsey\Uuid\UuidInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;

/**
 * Fix issue on incident that occurred on 26-28 July 2023.
 */
class RestoreSortedAssetCollectionDueToIncidentCommand extends Command
{
    public const START_INCIDENT_DATE = '2023-07-26T09:10:00+00:00';
    public const END_INCIDENT_DATE = '2023-07-28T13:00:00+00:00'; // in reality 11:00 UTC, but there is a grace period
    public const PRODUCT_TRACKING_TABLE_NAME = 'incident_product_asset_ordering_table';
    public const PRODUCT_MODEL_TRACKING_TABLE_NAME = 'incident_product_model_asset_ordering_table';
    private const BATCH_SIZE = 100;

    protected static $defaultName = 'pim:restore-sorted-assets-incident';
    protected static $defaultDescription = 'Restore asset collection sorted alphabetically due to an incident';

    public function __construct(
        private readonly BulkSaverInterface $productSaver,
        private readonly ProductRepository $productRepository,
        private readonly GrantedProductUpdater $productUpdater,
        private readonly BulkSaverInterface $productModelSaver,
        private readonly ProductModelRepositoryInterface $productModelRepository,
        private readonly GrantedProductModelUpdater $productModelUpdater,
        private readonly ValidatorInterface $validator,
        private readonly UnitOfWorkAndRepositoriesClearer $cacheClearer,
        private readonly Connection $connection,
        private readonly LoggerInterface $logger
    ) {
        parent::__construct();
    }

    /**
     * {@inheritdoc}
     */
    protected function configure()
    {
        $this
            ->addOption('dry-run')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $withDryRun = $input->getOption('dry-run');

        $this->restoreAssets($withDryRun);

        $numberProductVersions = $this->getNumberProductVersions();
        if ($withDryRun) {
            $output->writeln(sprintf('PIM-11120: "%d" products to restore. The detail is in the table "%s".', $numberProductVersions['affected'], self::PRODUCT_TRACKING_TABLE_NAME));
        } else {
            $numberProductVersions = $this->getNumberProductVersions();
            $output->writeln(sprintf('PIM-11120: "%d/%d" products restored. The detail is in the table "%s".', $numberProductVersions['restored'], $numberProductVersions['affected'], self::PRODUCT_TRACKING_TABLE_NAME));
        }


        $numberProductModelVersions = $this->getNumberProductModelVersions();
        if ($withDryRun) {
            $output->writeln(sprintf('PIM-11120: "%d" product model to restore. The detail is in the table "%s".', $numberProductModelVersions['affected'], self::PRODUCT_MODEL_TRACKING_TABLE_NAME));
        } else {
            $output->writeln(sprintf('PIM-11120: "%d/%d" product models restored. The detail is in the table "%s".', $numberProductModelVersions['restored'], $numberProductModelVersions['affected'], self::PRODUCT_MODEL_TRACKING_TABLE_NAME));
        }

        return 0;
    }

    /**
     * Public function to be called from ZDD migration.
     */
    public function restoreAssets(bool $withDryRun): void
    {
        $this->logger->notice('Creating tracking tables');
        $this->createProductTrackingTable();
        $this->createProductModelTrackingTable();

        $attributesIndexedByFlatFormatKey = $this->findAssetAttributes();
        if (empty($attributesIndexedByFlatFormatKey)) {
            $this->logger->notice('No asset in catalog, the migration will stop there.');

            return;
        }

        $productsWithVersions = $this->findProductVersionsDuringTheIncident();
        $productVersionsWithAssetSorted = $this->keepOnlyVersionsWithModifiedAndSortedAssets($productsWithVersions, $attributesIndexedByFlatFormatKey);
        $productVersionsWithAssetSortedAndNotModified = $this->keepOnlyProductVersionsWithUnmodifiedAssetsCollectionAfterIncident($productVersionsWithAssetSorted);
        $versionsTracked = $this->insertVersionIntoProductTrackingTable($productVersionsWithAssetSortedAndNotModified);
        $this->restoreProductAssetCollectionBeforeTheIncident($versionsTracked, $attributesIndexedByFlatFormatKey, $withDryRun);

        $productModelsWithVersions = $this->findProductModelVersionsDuringTheIncident();
        $productModelVersionsWithAssetSorted = $this->keepOnlyVersionsWithModifiedAndSortedAssets($productModelsWithVersions, $attributesIndexedByFlatFormatKey);
        $productModelVersionsWithAssetSortedAndNotModified = $this->keepOnlyProductModelVersionsWithUnmodifiedAssetsCollectionAfterIncident($productModelVersionsWithAssetSorted);
        $versionsTracked = $this->insertVersionIntoProductModelTrackingTable($productModelVersionsWithAssetSortedAndNotModified);
        $this->restoreProductModelAssetCollectionBeforeTheIncident($versionsTracked, $attributesIndexedByFlatFormatKey, $withDryRun);
    }

    /**
     * @return array<{key: string, code: string, locale: ?string, channel: ?string}>
     */
    private function findAssetAttributes(): array
    {
        $query = <<<SQL
            SELECT attr.code AS `key`, attr.code AS code, NULL AS locale, NULL AS channel
            FROM pim_catalog_attribute attr
            WHERE attribute_type = "pim_catalog_asset_collection"
            AND attr.is_localizable = false
            AND attr.is_scopable = false
            UNION
            SELECT CONCAT(attr.code, "-", locale.code) AS `key`, attr.code AS code, locale.code AS locale, NULL AS channel
            FROM pim_catalog_attribute attr
            JOIN pim_catalog_locale locale ON locale.is_activated = true
            WHERE attribute_type = "pim_catalog_asset_collection"
            AND attr.is_localizable = true
            AND attr.is_scopable = false
            UNION
            SELECT CONCAT(attr.code, "-", channel.code) AS `key`, attr.code AS code, NULL AS locale, channel.code AS channel
            FROM pim_catalog_attribute attr
            JOIN pim_catalog_channel channel
            WHERE attribute_type = "pim_catalog_asset_collection"
            AND attr.is_localizable = false
            AND attr.is_scopable = true
            UNION
            SELECT CONCAT(attr.code, "-", locale.code, "-", channel.code) AS `key`, attr.code AS code, locale.code AS locale, channel.code AS channel
            FROM pim_catalog_attribute attr
            JOIN pim_catalog_locale locale ON locale.is_activated = true
            JOIN pim_catalog_channel channel
            WHERE attribute_type = "pim_catalog_asset_collection"
            AND attr.is_localizable = true
            AND attr.is_scopable = true
        SQL;

        $results = $this->connection->executeQuery($query)->fetchAllAssociative();

        return array_combine(array_map(fn ($r) => $r['key'], $results), $results);
    }

    /**
     * There is no composite index on (resource_name, logged_at, resource_uuid).
     * Therefore, we will use search after on logged_at/id instead.
     *
     * It means that it will browse the index on logged_at and then perform Mysql row lookup to filter entities on resource_name.
     *
     * @return \Generator<array<string>>
     */
    private function findProductVersionsDuringTheIncident(): \Generator
    {
        $count = 0;
        $lastId = 0;
        $lastLoggedAt = \DateTimeImmutable::createFromFormat(\DateTimeInterface::ATOM, self::START_INCIDENT_DATE);
        $platform = $this->connection->getDatabasePlatform();

        $sql = <<<SQL
            SELECT id, resource_uuid, changeset, logged_at
            FROM pim_versioning_version
            WHERE 
                logged_at BETWEEN :min_logged_at AND :end_date_incident 
                AND id > :last_id
                AND resource_name = "Akeneo\\\Pim\\\Enrichment\\\Component\\\Product\\\Model\\\Product"
            ORDER BY logged_at, id
            LIMIT :limit
        SQL;

        while (true) {
            $count = $count + self::BATCH_SIZE;
            $this->logger->notice("Finding products, total at $count");

            $rows = $this->connection->fetchAllAssociative(
                $sql,
                [
                    'last_id' => $lastId,
                    'limit' => self::BATCH_SIZE,
                    'min_logged_at' =>  $lastLoggedAt,
                    'end_date_incident' =>  \DateTimeImmutable::createFromFormat(\DateTimeInterface::ATOM, self::END_INCIDENT_DATE)
                ],
                [
                    'last_id' => \PDO::PARAM_INT,
                    'limit' => \PDO::PARAM_INT,
                    'min_logged_at' => Types::DATETIME_IMMUTABLE,
                    'end_date_incident' => Types::DATETIME_IMMUTABLE
                ]
            );

            if (empty($rows)) {
                return;
            }

            $lastRow = $rows[count($rows) -1];
            $lastLoggedAt = Type::getType(Types::DATETIME_IMMUTABLE)->convertToPhpValue($lastRow['logged_at'], $platform);
            $lastId = (int) $lastRow['id'];

            foreach ($rows as $row) {
                yield [
                    'logged_at' => Type::getType(Types::DATETIME_IMMUTABLE)->convertToPhpValue($row['logged_at'], $platform),
                    'changeset' => unserialize($row['changeset']),
                    'uuid' => Uuid::fromBytes($row['resource_uuid']),
                    'id' => (int) $row['id']
                ];
            }
        }
    }

    /**
     * Same strategy as product, as there are missing an index on (resource_name, logged_at, resource_id).
     * Therefore, we will use search after on logged_at/id instead.
     *
     * @return \Generator<array<string>>
     */
    private function findProductModelVersionsDuringTheIncident(): \Generator
    {
        $count = 0;
        $lastId = 0;
        $lastLoggedAt = \DateTimeImmutable::createFromFormat(\DateTimeInterface::ATOM, self::START_INCIDENT_DATE);
        $platform = $this->connection->getDatabasePlatform();

        $sql = <<<SQL
            SELECT id, resource_id, changeset, logged_at
            FROM pim_versioning_version
            WHERE
                logged_at BETWEEN :min_logged_at AND :end_date_incident
                AND id > :last_id
                AND resource_name = "Akeneo\\\Pim\\\Enrichment\\\Component\\\Product\\\Model\\\ProductModel"
            ORDER BY logged_at, id
            LIMIT :limit
        SQL;

        while (true) {
            $count = $count + self::BATCH_SIZE;
            $this->logger->notice("Finding product models, total at $count");

            $rows = $this->connection->fetchAllAssociative(
                $sql,
                [
                    'last_id' => $lastId,
                    'limit' => self::BATCH_SIZE,
                    'min_logged_at' =>  $lastLoggedAt,
                    'end_date_incident' =>  \DateTimeImmutable::createFromFormat(\DateTimeInterface::ATOM, self::END_INCIDENT_DATE)
                ],
                [
                    'last_id' => \PDO::PARAM_INT,
                    'limit' => \PDO::PARAM_INT,
                    'min_logged_at' => Types::DATETIME_IMMUTABLE,
                    'end_date_incident' => Types::DATETIME_IMMUTABLE
                ]
            );

            if (empty($rows)) {
                return;
            }

            $lastRow = $rows[count($rows) -1];
            $lastLoggedAt = Type::getType(Types::DATETIME_IMMUTABLE)->convertToPhpValue($lastRow['logged_at'], $platform);
            $lastId = (int) $lastRow['id'];

            foreach ($rows as $row) {
                yield [
                    'logged_at' => Type::getType(Types::DATETIME_IMMUTABLE)->convertToPhpValue($row['logged_at'], $platform),
                    'changeset' => unserialize($row['changeset']),
                    'product_model_id' => (int) $row['resource_id'],
                    'id' => (int) $row['id']
                ];
            }
        }
    }

    private function keepOnlyVersionsWithModifiedAndSortedAssets(\Generator $versions, array $assetAttributesIndexedByFlatFormatKey): \Generator
    {
        foreach ($versions as $version) {
            $version['changeset'] = array_intersect_key($version['changeset'], $assetAttributesIndexedByFlatFormatKey);

            foreach ($version['changeset'] as $key => $assetChange) {
                $oldCodes = explode(',', $assetChange['old']);
                $newCodes = explode(',', $assetChange['new']);

                // alphabetically sort the old values to see if they match the new values (as MySQL will sort alphabetically
                // 99.99% of the time). If the old values sorted alphabetically are exactly the same as the new values,
                // it means this version was introduced by the PIM-11119 issue.
                // However:
                // - MySQL will order strings as follows: '_' < '3' < 'a|A' with the utf8mb4_unicode_ci collation
                // - but in PHP, even when using case-insensitive comparison, the numeric characters will be ordered before
                // the underscore character: '3' < '_' < 'a|A'
                // => In order to circumvent this issue, we replace the underscore by a '$' in the sorting algorithm,
                // as in PHP '$' < '3', and asset codes cannot contain this character.
                \usort(
                    $oldCodes,
                    static fn (string $a, string $b): int => \strcasecmp(
                        \str_replace('_', '$', $a),
                        \str_replace('_', '$', $b)
                    )
                );
                if ($newCodes !== $oldCodes) {
                    unset($version['changeset'][$key]);
                }
            }

            if (!empty($version['changeset'])) {
                yield $version;
            }
        };
    }

    private function keepOnlyProductVersionsWithUnmodifiedAssetsCollectionAfterIncident(\Generator $versions): \Generator
    {
        foreach ($versions as $version) {
            $changesetsAfterIncidents = $this->findProductVersionsAfterCurrentOne($version['uuid'], $version['id']);
            $changesetsAfterIncidents = array_map(fn ($changeset) => unserialize($changeset), $changesetsAfterIncidents);
            $version['changeset'] = array_diff_key($version['changeset'], ...$changesetsAfterIncidents);

            if (!empty($version['changeset'])) {
                yield $version;
            }
        };
    }

    private function keepOnlyProductModelVersionsWithUnmodifiedAssetsCollectionAfterIncident(\Generator $versions): \Generator
    {
        foreach ($versions as $version) {
            $changesetsAfterIncidents = $this->findProductModelVersionsAfterCurrentOne($version['product_model_id'], $version['id']);
            $changesetsAfterIncidents = array_map(fn ($changeset) => unserialize($changeset), $changesetsAfterIncidents);
            $version['changeset'] = array_diff_key($version['changeset'], ...$changesetsAfterIncidents);

            if (!empty($version['changeset'])) {
                yield $version;
            }
        };
    }

    private function findProductVersionsAfterCurrentOne(UuidInterface $uuid, int $versionId): array
    {
        $query = <<<SQL
            SELECT changeset
            FROM pim_versioning_version
            WHERE id > :id
                AND resource_uuid = :uuid
                AND resource_name = "Akeneo\\\Pim\\\Enrichment\\\Component\\\Product\\\Model\\\Product"
            ORDER BY id ASC
        SQL;

        return $this->connection->executeQuery($query, [
            'uuid' =>  $uuid->getBytes(),
            'id' =>  $versionId
        ])->fetchFirstColumn();
    }

    private function findProductModelVersionsAfterCurrentOne(int $productModelId, int $versionId): array
    {
        $query = <<<SQL
            SELECT changeset
            FROM pim_versioning_version
            WHERE id > :id
                AND resource_id = :resource_id
                AND resource_name = "Akeneo\\\Pim\\\Enrichment\\\Component\\\Product\\\Model\\\ProductModel"
            ORDER BY id ASC
        SQL;

        return $this->connection->executeQuery($query, [
            'resource_id' =>  (string) $productModelId,
            'id' =>  $versionId
        ])->fetchFirstColumn();
    }

    private function restoreProductAssetCollectionBeforeTheIncident(
        \Generator $versionsWithAssetSortedAndNotModified,
        array $attributesIndexedByFlatFormatKey,
        bool $withDryRun
    ): void {
        $totalCount = 0;
        foreach ($versionsWithAssetSortedAndNotModified as $version) {
            if ($withDryRun) {
                continue;
            }
            $values = [];
            foreach ($version['changeset'] as $key => $value) {
                $oldCodes = explode(',', $value['old']);
                $values[$attributesIndexedByFlatFormatKey[$key]['code']][] = [
                    'scope' => $attributesIndexedByFlatFormatKey[$key]['channel'],
                    'locale' => $attributesIndexedByFlatFormatKey[$key]['locale'],
                    'data' => $oldCodes,
                ];
            }

            $product = $this->productRepository->findOneByUuid($version['uuid']);
            if ($product === null) {
                continue;
            }
            $this->productUpdater->update($product, ['values' => $values,]);
            if ($this->validator->validate($product)->count() !== 0) {
                continue;
            }

            // some migrations failed due to our fantastic ORM and 2-way associations, let's save unitary
            try {
                $this->productSaver->saveAll([$product]);
            } catch (\Exception) {
                // an exception might occur in the (unlikely) case the product is linked to a category which only has
                // view permissions for every user group. In that case, we just skip the current product
            }

            $this->cacheClearer->clear();
            $this->markVersionAsRestored([$version['id']], self::PRODUCT_TRACKING_TABLE_NAME);

            $totalCount += 1;
            $this->logger->notice("Total count of products updated: $totalCount");
        }
    }

    private function restoreProductModelAssetCollectionBeforeTheIncident(
        \Generator $versionsWithAssetSortedAndNotModified,
        array $attributesIndexedByFlatFormatKey,
        bool $withDryRun
    ): void {
        $totalCount = 0;
        foreach ($versionsWithAssetSortedAndNotModified as $version) {
            if ($withDryRun) {
                continue;
            }
            $values = [];
            foreach ($version['changeset'] as $key => $value) {
                $oldCodes = explode(',', $value['old']);
                $values[$attributesIndexedByFlatFormatKey[$key]['code']][] = [
                    'scope' => $attributesIndexedByFlatFormatKey[$key]['channel'],
                    'locale' => $attributesIndexedByFlatFormatKey[$key]['locale'],
                    'data' => $oldCodes,
                ];
            }

            $productModel = $this->productModelRepository->find($version['product_model_id']);
            if ($productModel === null) {
                continue;
            }
            $this->productModelUpdater->update($productModel, ['values' => $values,]);

            if ($this->validator->validate($productModel)->count() !== 0) {
                continue;
            }
            try {
                $this->productModelSaver->saveAll([$productModel]);
            } catch (\Exception) {
                // as for products, do nothing
            }

            $this->cacheClearer->clear();
            $this->markVersionAsRestored([$version['id']], self::PRODUCT_MODEL_TRACKING_TABLE_NAME);
            $totalCount += 1;
            $this->logger->notice("Total count of product models updated: $totalCount");
        }
    }

    private function createProductTrackingTable(): void
    {
        $tableName = self::PRODUCT_TRACKING_TABLE_NAME;
        $this->connection->executeQuery("DROP TABLE IF EXISTS $tableName");

        $query = <<<SQL
            CREATE TABLE IF NOT EXISTS $tableName (
                version_id INT NOT NULL PRIMARY KEY,
                product_uuid BINARY(16) NOT NULL,
                logged_at datetime NOT NULL NOT NULL,
                changeset json NOT NULL,
                is_restored tinyint(1) DEFAULT 0, 
                KEY `product_uuid_idx` (`product_uuid`),
                KEY `logged_at_idx` (`logged_at`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
        SQL;

        $this->connection->executeQuery($query);
    }

    private function createProductModelTrackingTable(): void
    {
        $tableName = self::PRODUCT_MODEL_TRACKING_TABLE_NAME;
        $this->connection->executeQuery("DROP TABLE IF EXISTS $tableName");

        $query = <<<SQL
            CREATE TABLE IF NOT EXISTS $tableName (
                version_id INT NOT NULL PRIMARY KEY,
                product_model_id int NOT NULL,
                logged_at datetime NOT NULL NOT NULL,
                changeset json NOT NULL,
                is_restored tinyint(1) DEFAULT 0, 
                KEY `product_model_id_idx` (`product_model_id`),
                KEY `logged_at_idx` (`logged_at`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
        SQL;

        $this->connection->executeQuery($query);
    }

    private function insertVersionIntoProductTrackingTable(\Generator $versions):\Generator
    {
        $tableName = self::PRODUCT_TRACKING_TABLE_NAME;

        foreach ($versions as $version) {
            $query = <<<SQL
                INSERT INTO $tableName
                (version_id, product_uuid, logged_at, changeset)
                VALUES (:version_id, :product_uuid, :logged_at, :changeset)
            SQL;

            $this->connection->executeQuery(
                $query,
                [
                    'version_id' =>  $version['id'],
                    'product_uuid' =>  $version['uuid']->getBytes(),
                    'logged_at' => $version['logged_at'],
                    'changeset' => \json_encode($version['changeset'])
                ],
                [
                    'logged_at' => Types::DATETIME_IMMUTABLE,
                    'product_uuid' => \PDO::PARAM_STR
                ]
            );

            yield $version;
        }
    }

    private function insertVersionIntoProductModelTrackingTable(\Generator $versions):\Generator
    {
        $tableName = self::PRODUCT_MODEL_TRACKING_TABLE_NAME;

        foreach ($versions as $version) {
            $query = <<<SQL
                INSERT INTO $tableName
                (version_id, product_model_id, logged_at, changeset)
                VALUES (:version_id, :product_model_id, :logged_at, :changeset)
            SQL;

            $this->connection->executeQuery(
                $query,
                [
                    'version_id' =>  $version['id'],
                    'product_model_id' =>  $version['product_model_id'],
                    'logged_at' => $version['logged_at'],
                    'changeset' => \json_encode($version['changeset'])
                ],
                [
                    'logged_at' => Types::DATETIME_IMMUTABLE,
                    'product_uuid' => \PDO::PARAM_STR
                ]
            );

            yield $version;
        }
    }

    public function getNumberProductVersions(): array
    {
        return $this->connection->fetchAssociative(
            'select count(*) as affected, sum(is_restored) as restored from incident_product_asset_ordering_table'
        );
    }

    public function getNumberProductModelVersions(): array
    {
        return $this->connection->fetchAssociative(
            'select count(*) as affected, sum(is_restored) as restored from incident_product_model_asset_ordering_table'
        );
    }

    private function markVersionAsRestored(array $versionIdsToRestore, string $tableName)
    {
        $this->connection->executeQuery(
            "UPDATE $tableName SET is_restored = 1 WHERE version_id IN (:version_ids)",
            ['version_ids' =>  $versionIdsToRestore],
            ['version_ids' => Connection::PARAM_INT_ARRAY]
        );
    }
}
