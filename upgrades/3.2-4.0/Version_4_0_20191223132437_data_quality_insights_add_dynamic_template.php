<?php declare(strict_types=1);

namespace Pim\Upgrade\Schema;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;
use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

final class Version_4_0_20191223132437_data_quality_insights_add_dynamic_template extends AbstractMigration implements ContainerAwareInterface
{
    /** @var ContainerInterface */
    private $container;

    public function up(Schema $schema) : void
    {
        $this->disableMigrationWarning();

        $builder = $this->container->get('akeneo_elasticsearch.client_builder');
        $hosts = [$this->container->getParameter('index_hosts')];

        $client = $builder->setHosts($hosts)->build()->indices();

        $existingMapping = $client->getMapping(['index' => $this->container->getParameter('product_and_product_model_index_name')]);

        $existingDynamicTemplate = current($existingMapping)['mappings']['dynamic_templates'];
        $newDynamicTemplate = array_merge($existingDynamicTemplate, [
            [
                'rates_structure' => [
                    'path_match' => 'rates.*',
                    'match_mapping_type' => 'object',
                    'mapping' => [
                        'type' => 'object'
                    ]
                ]
            ],
            [
                'rates' => [
                    'path_match' => 'rates.*',
                    'mapping' => [
                        'type' => 'keyword'
                    ]
                ]
            ]
        ]);

        $client->putMapping([
            'index' => $this->container->getParameter('product_and_product_model_index_name'),
            'body' => [
                'dynamic_templates' => $newDynamicTemplate
            ]
        ]);

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
        $this->addSql('SELECT * FROM oro_user LIMIT 1');
    }
}
