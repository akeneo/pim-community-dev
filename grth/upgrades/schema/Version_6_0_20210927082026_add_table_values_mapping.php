<?php

declare(strict_types=1);

namespace Pim\Upgrade\Schema;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;
use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\Yaml\Yaml;

final class Version_6_0_20210927082026_add_table_values_mapping extends AbstractMigration implements ContainerAwareInterface
{
    private const TABLE_MAPPING_FILE = __DIR__ . '/../../src/Akeneo/Pim/TableAttribute/back/Infrastructure/Symfony/Resources/config/elasticsearch/table_attribute_mapping.yml';
    private ContainerInterface $container;

    public function getDescription(): string
    {
        return 'Add the mapping for the table values in the product and product model index';
    }

    public function up(Schema $schema): void
    {
        $this->disableMigrationWarning();

        $builder = $this->container->get('akeneo_elasticsearch.client_builder');
        $hosts = [$this->container->getParameter('index_hosts')];
        $client = $builder->setHosts($hosts)->build()->indices();

        $existingMapping = $client->getMapping(['index' => $this->container->getParameter('product_and_product_model_index_name')]);
        $existingDynamicTemplate = current($existingMapping)['mappings']['dynamic_templates'];

        if ($this->tableMappingIsAlreadySet($existingDynamicTemplate)) {
            return;
        }

        $tableValuesMapping = Yaml::parseFile(static::TABLE_MAPPING_FILE)['mappings']['dynamic_templates'];
        $newDynamicTemplate = array_merge($existingDynamicTemplate, $tableValuesMapping);

        $client->putMapping([
            'index' => $this->container->getParameter('product_and_product_model_index_name'),
            'body' => ['dynamic_templates' => $newDynamicTemplate],
        ]);
    }

    private function tableMappingIsAlreadySet(array $existingDynamicTemplate): bool
    {
        foreach ($existingDynamicTemplate as $fields) {
            if (array_key_exists('table_values', $fields)) {
                $this->write('Mapping is already set. Nothing to do.');

                return true;
            }
        }

        return false;
    }

    public function down(Schema $schema): void
    {
        $this->throwIrreversibleMigrationException();
    }

    public function setContainer(ContainerInterface $container = null): void
    {
        $this->container = $container;
    }

    private function disableMigrationWarning(): void
    {
        $this->addSql('SELECT 1');
    }
}
