<?php declare(strict_types=1);

namespace Pim\Upgrade\Schema;

use Akeneo\AssetManager\Infrastructure\Symfony\Command\IndexAllAssetsOnTemporaryIndexCommand;
use Doctrine\DBAL\Schema\Schema;
use Doctrine\DBAL\Types\Types;
use Doctrine\Migrations\AbstractMigration;
use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Webmozart\Assert\Assert;


final class Version_5_0_20201207132648_prepare_new_asset_index extends AbstractMigration implements ContainerAwareInterface
{
    private ContainerInterface $container;

    public function up(Schema $schema) : void
    {
        $this->disableMigrationWarning();

        $client = $this->container->get('akeneo_assetmanager.client.asset_temporary');

        Assert::false($client->hasIndex(), 'An index already exist with this name.');
        Assert::false($client->hasIndexForAlias(), 'An alias already exist with this name.');
        $this->container->get('akeneo_assetmanager.client.asset_temporary')->resetIndex();

        $sql = 'INSERT INTO pim_configuration (`code`, `values`) VALUES (:code, :values);';

        $this->addSql(
            $sql,
            [
                'code' => IndexAllAssetsOnTemporaryIndexCommand::CONFIGURATION_CODE,
                'values' => ['status' => 'todo'],
            ],
            ['values' => Types::JSON]
        );
    }

    public function down(Schema $schema) : void
    {
        $this->throwIrreversibleMigrationException();
    }

    public function setContainer(ContainerInterface $container = null)
    {
        $this->container = $container;
    }

    /**
     * Function that does a non altering operation on the DB using SQL to hide the doctrine warning stating that no
     * sql query has been made to the db during the migration process.
     */
    private function disableMigrationWarning()
    {
        $this->addSql('SELECT 1');
    }
}
