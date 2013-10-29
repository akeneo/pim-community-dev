<?php

namespace Oro\Bundle\DataGridBundle\Extension\Formatter;

use Oro\Bundle\DataGridBundle\Datagrid\Common\MetadataObject;
use Oro\Bundle\DataGridBundle\Datagrid\Common\ResultsObject;
use Oro\Bundle\DataGridBundle\Datagrid\Common\DatagridConfiguration;
use Oro\Bundle\DataGridBundle\Extension\AbstractExtension;
use Oro\Bundle\DataGridBundle\Extension\Formatter\Property\PropertyInterface;

class FormatterExtension extends AbstractExtension
{
    /** @var PropertyInterface[] */
    protected $properties = [];

    /**
     * {@inheritDoc}
     */
    public function isApplicable(DatagridConfiguration $config)
    {
        $columns    = $config->offsetGetByPath(Configuration::COLUMNS_PATH, []);
        $properties = $config->offsetGetByPath(Configuration::PROPERTIES_PATH, []);
        $applicable = $columns || $properties;

        // validate extension configuration
        $this->validateConfiguration(
            new Configuration(array_keys($this->properties)),
            ['columns_and_properties' => array_merge($columns, $properties)]
        );

        return $applicable;
    }

    /**
     * {@inheritDoc}
     */
    public function visitResult(DatagridConfiguration $config, ResultsObject $result)
    {
        $rows       = (array)$result->offsetGetOr('data', []);
        $columns    = $config->offsetGetByPath(Configuration::COLUMNS_PATH, []);
        $properties = $config->offsetGetByPath(Configuration::PROPERTIES_PATH, []);
        $toProcess  = array_merge($columns, $properties);

        foreach ($rows as $key => $row) {
            $record = new ResultRecord($row);
            foreach ($toProcess as $name => $config) {
                $property   = $this->getPropertyObject($name, $config);
                $row[$name] = $property->getValue($record);
            }
            // result row will contains only processed rows
            $rows[$key] = array_intersect_key($row, array_flip(array_keys($toProcess)));
        }

        $result->offsetSet('data', $rows);
    }

    /**
     * {@inheritDoc}
     */
    public function visitMetadata(DatagridConfiguration $config, MetadataObject $data)
    {
        // get only columns here because columns will be represented on frontend
        $columns = $config->offsetGetByPath(Configuration::COLUMNS_PATH, []);

        $propertiesMetadata = [];
        foreach ($columns as $name => $fieldConfig) {
            $metadata             = $this->getPropertyObject($name, $fieldConfig)->getMetadata();
            $propertiesMetadata[] = $metadata;
        }

        $data->offsetAddToArray('columns', $propertiesMetadata);

        // grid options passed under "options" node
        $options = array_merge($data->offsetGetOr('options', []), $config->offsetGetOr('options', []));
        $data->offsetSet('options', $options);
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
     * @param string $name
     * @param array  $config
     *
     * @return PropertyInterface
     */
    protected function getPropertyObject($name, array $config)
    {
        $config[PropertyInterface::NAME_KEY] = $name;
        $property                            = $this->properties[$config[Configuration::TYPE_KEY]];
        $property->init($config);

        return $property;
    }
}
