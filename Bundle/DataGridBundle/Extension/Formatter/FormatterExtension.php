<?php

namespace Oro\Bundle\DataGridBundle\Extension\Formatter;

use Oro\Bundle\DataGridBundle\Extension\AbstractExtension;
use Oro\Bundle\DataGridBundle\Extension\Formatter\Property\PropertyInterface;

class FormatterExtension extends AbstractExtension
{
    /** @var PropertyInterface[] */
    protected $properties = [];

    /**
     * {@inheritDoc}
     */
    public function isApplicable(array $config)
    {
        $columns    = $this->accessor->getValue($config, Configuration::COLUMNS_PATH) ? : [];
        $properties = $this->accessor->getValue($config, Configuration::PROPERTIES_PATH) ? : [];
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
    public function visitResult(array $config, \stdClass $result)
    {
        $rows       = (array)$result->rows;
        $results    = [];
        $columns    = $this->accessor->getValue($config, Configuration::COLUMNS_PATH) ? : [];
        $properties = $this->accessor->getValue($config, Configuration::PROPERTIES_PATH) ? : [];
        $toProcess  = array_merge($columns, $properties);

        foreach ($rows as $row) {
            $resultRecord = [];
            $record       = new ResultRecord($row);

            foreach ($toProcess as $name => $config) {
                $property            = $this->getPropertyObject($name, $config);
                $resultRecord[$name] = $property->getValue($record);
            }

            $results[] = $resultRecord;
        }

        $result->rows = $results;
    }

    /**
     * {@inheritDoc}
     */
    public function visitMetadata(array $config, \stdClass $data)
    {
        // get only columns here because columns will be represented on frontend
        $columns = $this->accessor->getValue($config, Configuration::COLUMNS_PATH) ? : [];

        $propertiesMetadata = [];
        foreach ($columns as $name => $fieldConfig) {
            $metadata                  = $this->getPropertyObject($name, $fieldConfig)->getMetadata();
            $propertiesMetadata[$name] = $metadata;
        }

        $data->columns = array_merge(
            isset($data->columns) && is_array($data->columns) ? $data->columns : [],
            $propertiesMetadata
        );
    }

    /**
     * Add property to array of available properties
     *
     * @param string            $name
     * @param PropertyInterface $property
     *
     * @return $this
     */
    public function addProperty($name, PropertyInterface $property)
    {
        $this->properties[$name] = $property;

        return $this;
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

        $type = $this->accessor->getValue($config, sprintf('[%s]', Configuration::TYPE_KEY));

        $property = $this->properties[$type];
        $property->init($config);

        return clone $property;
    }
}
