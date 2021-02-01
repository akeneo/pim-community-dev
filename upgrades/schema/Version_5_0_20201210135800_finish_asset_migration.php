<?php declare(strict_types=1);

namespace Pim\Upgrade\Schema;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\DBAL\Types\Type;
use Doctrine\DBAL\Types\Types;
use Doctrine\Migrations\AbstractMigration;
use Elasticsearch\Client;
use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Webmozart\Assert\Assert;

/**
 * @TODO: to remove before v5 release!
 *
 * Finish the asset reindexing migration. Before the migration we should have:
 *  - an asset alias with an "old" index
 *  - a temporary asset alias with a "new" index
 * We need to switch the asset alias to the new index, remove the old index, and remove the temporary alias.
 */
final class Version_5_0_20201210135800_finish_asset_migration extends AbstractMigration implements ContainerAwareInterface
{
    private ContainerInterface $container;
    private Client $nativeClient;

    private const CONFIGURATION_CODE = 'reindex_assets_eb7f3b50-98d0-43f5-bde9-edc505241e6c';

    public function up(Schema $schema) : void
    {
        Assert::false($this->doesConfigurationExist(), 'The asset migration is not finished! We cannot terminate it.');

        $clientBuilder = $this->container->get('akeneo_elasticsearch.client_builder')->setHosts([
            $this->container->getParameter('index_hosts')
        ]);
        $this->nativeClient = $clientBuilder->build();

        $mainAliasName = $this->container->get('akeneo_assetmanager.client.asset')->getIndexName();
        $oldIndexName = $this->getIndexNameFromAlias($mainAliasName);
        Assert::notNull($oldIndexName);

        $temporaryAliasName = sprintf('%s_temporary', $this->container->getParameter('asset_index_name'));
        $newIndexName = $this->getIndexNameFromAlias($temporaryAliasName);
        Assert::notNull($newIndexName);
        Assert::notSame($oldIndexName, $newIndexName);

        $this->switchMainAliasAndRemoveOldIndex($mainAliasName, $oldIndexName, $newIndexName);
        $this->deleteTemporaryAlias($temporaryAliasName, $newIndexName);
    }

    public function down(Schema $schema) : void
    {
        $this->throwIrreversibleMigrationException();
    }

    public function setContainer(ContainerInterface $container = null)
    {
        $this->container = $container;
    }

    private function doesConfigurationExist(): bool
    {
        $sql = <<<SQL
SELECT EXISTS(
    SELECT 1 FROM pim_configuration WHERE code = :code
) as is_existing
SQL;
        $statement = $this->connection->executeQuery($sql, [
            'code' => self::CONFIGURATION_CODE,
        ]);

        $platform = $this->connection->getDatabasePlatform();
        $result = $statement->fetch(\PDO::FETCH_ASSOC);
        $statement->closeCursor();

        return Type::getType(Types::BOOLEAN)->convertToPhpValue($result['is_existing'], $platform);
    }

    private function getIndexNameFromAlias(string $aliasName): ?string
    {
        $indices = $this->nativeClient->indices();
        $aliases = $indices->getAlias(['name' => $aliasName]);

        return array_keys($aliases)[0] ?? null;
    }

    private function switchMainAliasAndRemoveOldIndex(
        string $mainAliasName,
        string $oldIndexName,
        string $newIndexName
    ): void {
        $result = $this->nativeClient->indices()->updateAliases([
            'body' => [
                "actions" => [
                    [
                        'add' => [
                            'index' => $newIndexName,
                            'alias' => $mainAliasName,
                        ]
                    ],
                    [
                        'remove_index' => [
                            'index' => $oldIndexName
                        ]
                    ],
                ]
            ]
        ]);
        Assert::true($result['acknowledged']);
    }

    private function deleteTemporaryAlias(string $temporaryAliasName, string $newIndexName): void
    {
        $result = $this->nativeClient->indices()->deleteAlias([
            'index' => $newIndexName,
            'name' => $temporaryAliasName,
        ]);

        Assert::true($result['acknowledged']);
    }
}
