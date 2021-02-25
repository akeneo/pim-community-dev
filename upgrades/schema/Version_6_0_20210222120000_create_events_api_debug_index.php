<?php

declare(strict_types=1);

namespace Pim\Upgrade\Schema;

use Akeneo\Tool\Bundle\ElasticsearchBundle\Client;
use Akeneo\Tool\Bundle\ElasticsearchBundle\IndexConfiguration\Loader;
use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;
use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBag;

/**
 * @copyright 2021 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
final class Version_6_0_20210222120000_create_events_api_debug_index extends AbstractMigration
implements ContainerAwareInterface
{
    /** @var ContainerInterface */
    private $container;

    public function setContainer(ContainerInterface $container = null)
    {
        $this->container = $container;
    }

    public function up(Schema $schema): void
    {
        $builder = $this->container->get('akeneo_elasticsearch.client_builder');
        $hosts = [$this->container->getParameter('index_hosts')];
        $ceDir = $this->container->getParameter('pim_ce_dev_src_folder_location');
        $index = $this->container->getParameter('events_api_debug_index_name');
        $configurationLoader = new Loader(
            [
                "{$ceDir}/src/Akeneo/Connectivity/Connection/back/Infrastructure/Symfony/Resources/elasticsearch/events_api_debug_mapping.yml",
            ],
            new ParameterBag()
        );

        $client = new Client($builder, $configurationLoader, $hosts, $index);
        $client->createIndex();
    }

    public function down(Schema $schema): void
    {
        $this->throwIrreversibleMigrationException();
    }
}
