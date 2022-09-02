<?php

declare(strict_types=1);

namespace Pim\Upgrade\Schema;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;
use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\Yaml\Yaml;

final class Version_7_0_20220128094051_add_table_measurement_value_mapping extends AbstractMigration implements ContainerAwareInterface
{
    private ContainerInterface $container;

    public function getDescription(): string
    {
        return 'Add the mapping for the table measurement value in the product and product model index';
    }

    public function up(Schema $schema): void
    {
        $this->disableMigrationWarning();

        $builder = $this->container->get('akeneo_elasticsearch.client_builder');
        $hosts = [$this->container->getParameter('index_hosts')];
        $client = $builder->setHosts($hosts)->build()->indices();

        $existingMapping = $client->getMapping(['index' => $this->container->getParameter('product_and_product_model_index_name')]);
        $existingDynamicTemplate = current($existingMapping)['mappings']['dynamic_templates'];

        if ($this->mappingIsAlreadySet($existingDynamicTemplate)) {
            return;
        }

        $newDynamicTemplate = \array_merge($existingDynamicTemplate, $this->getNewMappingDynamicTemplates());

        $client->putMapping([
            'index' => $this->container->getParameter('product_and_product_model_index_name'),
            'body' => ['dynamic_templates' => $newDynamicTemplate],
        ]);
    }

    private function mappingIsAlreadySet(array $existingDynamicTemplate): bool
    {
        foreach ($existingDynamicTemplate as $fields) {
            if (\array_key_exists('table_values_measurement', $fields)) {
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

    private function getNewMappingDynamicTemplates(): array
    {
        return Yaml::parse(<<<YAML
-
  table_values_measurement:
    path_match: 'table_values.*.value-measurement'
    mapping:
      type: 'double'
YAML);
    }
}
