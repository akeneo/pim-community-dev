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

        $newDynamicTemplate = array_merge($existingDynamicTemplate, $this->getTableAttributeMappingDynamicTemplates());

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

    private function getTableAttributeMappingDynamicTemplates(): array
    {
        return Yaml::parse(<<<YAML
-
  table_values:
    path_match: 'table_values.*'
    path_unmatch: 'table_values.*.*'
    match_mapping_type: 'object'
    mapping:
      type: 'nested'
-
  table_values_locale:
    path_match: 'table_values.*.locale'
    mapping:
      type: 'keyword'
-
  table_values_channel:
    path_match: 'table_values.*.channel'
    mapping:
      type: 'keyword'
-
  table_values_row:
    path_match: 'table_values.*.row'
    mapping:
      type: 'keyword'
      normalizer: 'identifier_normalizer'
-
  table_values_column:
    path_match: 'table_values.*.column'
    mapping:
      type: 'keyword'
      normalizer: 'identifier_normalizer'
-
  table_values_is_column_complete:
    path_match: 'table_values.*.is_column_complete'
    mapping:
      type: 'boolean'
-
  table_values_number:
    path_match: 'table_values.*.value-number'
    mapping:
      type: 'double'
-
  table_values_text:
    path_match: 'table_values.*.value-text'
    mapping:
      type: 'keyword'
      normalizer: 'text_normalizer'
-
  table_values_boolean:
    path_match: 'table_values.*.value-boolean'
    mapping:
      type: 'boolean'
-
  table_values_select:
    path_match: 'table_values.*.value-select'
    mapping:
      type: 'keyword'
      normalizer: 'attribute_option_normalizer'
YAML);
    }
}
