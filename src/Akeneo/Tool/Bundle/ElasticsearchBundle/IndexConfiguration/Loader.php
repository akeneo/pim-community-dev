<?php

namespace Akeneo\Tool\Bundle\ElasticsearchBundle\IndexConfiguration;

use Symfony\Component\Yaml\Parser;

/**
 * Elasticsearch configuration loader. Allows to load "index settings", "mappings" and "aliases".
 * To learn more, see {@link https://www.elastic.co/guide/en/elasticsearch/reference/current/indices-create-index.html}
 *
 * This loader is able to load the configuration from several different files. For instance, from the default
 * Akeneo file, and from a custom project file.
 *
 * @author    Julien Janvier <j.janvier@gmail.com>
 * @copyright 2017 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class Loader
{
    /** @var array */
    private $configurationFiles;

    /**
     * @param array $configurationFiles
     */
    public function __construct(array $configurationFiles)
    {
        $this->configurationFiles = $configurationFiles;
    }

    /**
     * Load the Elasticsearch index configuration from multiple YAML files.
     *
     * @return IndexConfiguration
     * @throws \Exception
     */
    public function load()
    {
        $settings = [];
        $mappings = [];
        $aliases = [];
        $yaml = new Parser();

        foreach ($this->configurationFiles as $configurationFile) {
            if (!is_readable($configurationFile)) {
                throw new \Exception(
                    sprintf('The elasticsearch configuration file "%s" is not readable.', $configurationFile)
                );
            }

            $configuration = $yaml->parse(file_get_contents($configurationFile));

            if (isset($configuration['settings'])) {
                $settings = array_replace_recursive($settings, $configuration['settings']);
            }
            if (isset($configuration['mappings'])) {
                $mappings = $this->mergeMappings($mappings, $configuration['mappings']);
            }
            if (isset($configuration['aliases'])) {
                $aliases = array_replace_recursive($aliases, $configuration['aliases']);
            }
        }

        return new IndexConfiguration($settings, $mappings, $aliases);
    }

    /**
     * Mappings must be merged considering three cases:
     * - 'properties' is an associative array and new definitions must replace old ones if they have the same key
     * - 'dynamic_templates' is an indexed array and new definitions must always be added
     * - other keys, merged with array_replace policy
     */
    private function mergeMappings(array $originalMappings, array $additionalMappings): array
    {
        foreach ($additionalMappings as $indexName => $definitions) {
            if (isset($definitions['properties'])) {
                $originalProperties = isset($originalMappings[$indexName]['properties']) ?
                    $originalMappings[$indexName]['properties'] : [];

                $originalMappings[$indexName]['properties'] = array_replace_recursive(
                    $originalProperties,
                    $definitions['properties']
                );
            }
            if (isset($definitions['dynamic_templates'])) {
                $originalTemplates = isset($originalMappings[$indexName]['dynamic_templates']) ?
                    $originalMappings[$indexName]['dynamic_templates'] : [];

                $originalMappings[$indexName]['dynamic_templates'] = array_merge_recursive(
                    $originalTemplates,
                    $definitions['dynamic_templates']
                );
            }
            // hacky stuff to merge all other mappings
            $otherMappings = $definitions;
            unset($otherMappings['properties']);
            unset($otherMappings['dynamic_templates']);
            $originalMappings[$indexName] = array_replace_recursive(
                $originalMappings[$indexName] ?? [],
                $otherMappings
            );
        }

        return $originalMappings;
    }
}
