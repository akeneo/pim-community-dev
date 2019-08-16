<?php

namespace Oro\Bundle\DataGridBundle\Extension\Formatter;

use Oro\Bundle\DataGridBundle\Datagrid\Common\DatagridConfiguration;
use Oro\Bundle\DataGridBundle\Datagrid\Common\MetadataIterableObject;
use Oro\Bundle\DataGridBundle\Datagrid\Common\ResultsIterableObject;
use Oro\Bundle\DataGridBundle\Extension\AbstractExtension;
use Oro\Bundle\DataGridBundle\Extension\Formatter\Property\PropertyConfiguration;
use Oro\Bundle\DataGridBundle\Extension\Formatter\Property\PropertyInterface;
use Symfony\Component\Translation\TranslatorInterface;

class FormatterExtension extends AbstractExtension
{
    /** @var PropertyInterface[] */
    protected $properties = [];

    /** @var TranslatorInterface */
    protected $translator;

    public function __construct(TranslatorInterface $translator)
    {
        $this->translator = $translator;
    }

    /**
     * {@inheritDoc}
     */
    public function isApplicable(DatagridConfiguration $config)
    {
        $columns = $config->offsetGetOr(Configuration::COLUMNS_KEY, []);
        $properties = $config->offsetGetOr(Configuration::PROPERTIES_KEY, []);
        $applicable = $columns || $properties;
        $this->processConfigs($config);

        return $applicable;
    }

    /**
     * Validate configs nad fill default values
     *
     * @param DatagridConfiguration $config
     */
    public function processConfigs(DatagridConfiguration $config)
    {
        $columns = $config->offsetGetOr(Configuration::COLUMNS_KEY, []);
        $properties = $config->offsetGetOr(Configuration::PROPERTIES_KEY, []);

        // validate extension configuration and normalize by setting default values
        $columnsNormalized = $this->validateConfigurationByType($columns, Configuration::COLUMNS_KEY);
        $propertiesNormalized = $this->validateConfigurationByType($properties, Configuration::PROPERTIES_KEY);

        // replace config values by normalized, extra keys passed directly
        $config->offsetSet(Configuration::COLUMNS_KEY, array_replace_recursive($columns, $columnsNormalized))
            ->offsetSet(Configuration::PROPERTIES_KEY, array_replace_recursive($properties, $propertiesNormalized));
    }

    /**
     * {@inheritDoc}
     */
    public function visitResult(DatagridConfiguration $config, ResultsIterableObject $result)
    {
        $rows = (array)$result->offsetGetOr('data', []);

        if (isset($rows['totalRecords'])) {
            $result->offsetSet('totalRecords', $rows['totalRecords']);
            $rows = $rows['data'];
        }

        $columns = $config->offsetGetOr(Configuration::COLUMNS_KEY, []);
        $properties = $config->offsetGetOr(Configuration::PROPERTIES_KEY, []);
        $toProcess = array_replace($columns, $properties);

        foreach ($rows as $key => $row) {
            $currentRow = [];

            foreach ($toProcess as $name => $config) {
                $config = PropertyConfiguration::createNamed($name, $config);
                $property = $this->getPropertyObject($config);
                $currentRow[$name] = $property->getValue($row);
            }
            $rows[$key] = $currentRow;
        }

        $result->offsetSet('data', $rows);
    }

    /**
     * {@inheritDoc}
     */
    public function visitMetadata(DatagridConfiguration $config, MetadataIterableObject $data)
    {
        // get only columns here because columns will be represented on frontend
        $columns = $config->offsetGetOr(Configuration::COLUMNS_KEY, []);

        $propertiesMetadata = [];
        foreach ($columns as $name => $fieldConfig) {
            $fieldConfig = PropertyConfiguration::createNamed($name, $fieldConfig);
            $metadata = $this->getPropertyObject($fieldConfig)->getMetadata();

            // translate label on backend
            $metadata['label'] = $this->translator->trans($metadata['label']);
            $propertiesMetadata[] = $metadata;
        }

        $data->offsetAddToArray('columns', $propertiesMetadata);
    }

    /**
     * Add property to array of available properties, usually called by DIC
     *
     * @param string            $name
     * @param PropertyInterface $property
     */
    public function registerProperty($name, PropertyInterface $property)
    {
        $this->properties[$name] = $property;
    }

    /**
     * Returns prepared property object
     *
     * @param PropertyConfiguration $config
     *
     * @return PropertyInterface
     */
    protected function getPropertyObject(PropertyConfiguration $config)
    {
        $property = $this->properties[$config->offsetGet(Configuration::TYPE_KEY)]->init($config);

        return $property;
    }

    /**
     * Validates specified type configuration
     *
     * @param array  $config
     * @param string $type
     *
     * @return array
     */
    protected function validateConfigurationByType($config, $type)
    {
        $registeredTypes = array_keys($this->properties);
        $configuration = new Configuration($registeredTypes, $type);

        return parent::validateConfiguration($configuration, [$type => $config]);
    }
}
