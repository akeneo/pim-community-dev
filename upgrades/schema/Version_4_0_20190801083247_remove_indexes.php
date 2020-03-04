<?php declare(strict_types=1);

namespace Pim\Upgrade\Schema;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;
use Elasticsearch\Namespaces\IndicesNamespace;
use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

final class Version_4_0_20190801083247_remove_indexes extends AbstractMigration  implements ContainerAwareInterface
{
    /** @var ContainerInterface */
    private $container;

    public function up(Schema $schema) : void
    {
        $this->addSql('SELECT "disable migration warning"');

        $builder = $this->container->get('akeneo_elasticsearch.client_builder');
        $hosts = [$this->container->getParameter('index_hosts')];

        $client = $builder->setHosts($hosts)->build()->indices();

        $this->deleteAliasAndIndex($client, 'akeneo_pim_product');
        $this->deleteAliasAndIndex($client, 'akeneo_pim_product_model');
        $this->deleteAliasAndIndex($client, 'akeneo_pim_published_product_and_product_model');
    }

    public function down(Schema $schema) : void
    {
        $this->throwIrreversibleMigrationException();
    }

    public function setContainer(ContainerInterface $container = null)
    {
        $this->container = $container;
    }

    private function deleteAliasAndIndex(IndicesNamespace $client, $aliasName) {
        if ($client->existsAlias(['name' => $aliasName ])) {
            echo sprintf("Deleting alias \"%s\" and index.\n", $aliasName);
            $alias = $client->getAlias(['name' => $aliasName ]);
            $index = array_keys($alias)[0];
            $client->deleteAlias([ 'index' => $index, 'name' => $aliasName ]);

            return $client->delete(['index' => $index]);
        }

        echo sprintf(
            "The alias \"%s\" does not exist. This could be because it's part of the Enterprise Edition.\n",
            $aliasName
        );
    }
}
