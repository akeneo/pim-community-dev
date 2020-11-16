<?php declare(strict_types=1);

namespace Pim\Upgrade\Schema;

use Akeneo\Tool\Bundle\ElasticsearchBundle\Client;
use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;
use Elasticsearch\ClientBuilder;
use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;


final class Version_5_0_20201116133700_dqi_put_elasticsearch_mapping extends AbstractMigration implements ContainerAwareInterface
{
    private ContainerInterface $container;

    public function up(Schema $schema) : void
    {
        $this->disableMigrationWarning();

        $builder = $this->container->get('akeneo_elasticsearch.client_builder');
        $hosts = [$this->container->getParameter('index_hosts')];

        $client = $builder->setHosts($hosts)->build()->indices();

        $existingMapping = $client->getMapping(['index' => $this->container->getParameter('product_and_product_model_index_name')]);

        if(false === is_array($existingMapping) || false === isset(current($existingMapping)['mappings']['dynamic_templates']))
        {
            throw new \RuntimeException('Unable to retrieve existing mapping.');
        }

        $existingDynamicTemplate = current($existingMapping)['mappings']['dynamic_templates'];

        $newDynamicTemplate = array_merge($existingDynamicTemplate, [
            [
                'data_quality_insights_structure' => [
                    'path_match' => 'data_quality_insights.*',
                    'match_mapping_type' => 'object',
                    'mapping' => [
                        'type' => 'object'
                    ],
                ],
            ],
            [
                'data_quality_insights_key_indicators' => [
                    'path_match' => 'data_quality_insights.key_indicators.*',
                    'mapping' => [
                        'type' => 'boolean'
                    ],
                ],
            ],
            [
                'data_quality_insights_scores' => [
                    'path_match' => 'data_quality_insights.scores.*',
                    'mapping' => [
                        'type' => 'short'
                    ],
                ],
            ],
        ]);

        $client->putMapping([
            'index' => $this->container->getParameter('product_and_product_model_index_name'),
            'body' => [
                'dynamic_templates' => $newDynamicTemplate
            ]
        ]);
    }

    public function setContainer(ContainerInterface $container = null)
    {
        $this->container = $container;
    }

    public function down(Schema $schema) : void
    {
        $this->throwIrreversibleMigrationException();
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
