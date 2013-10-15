<?php

namespace Oro\Bundle\DataGridBundle\Extension\Formatter;

use Oro\Bundle\DataGridBundle\Extension\AbstractExtension;
use Oro\Bundle\DataGridBundle\Extension\Formatter\Configuration;
use Oro\Bundle\DataGridBundle\Extension\Formatter\Property\PropertyInterface;

use Symfony\Component\Config\Definition\Processor;

class FormatterExtension extends AbstractExtension
{
    /**
     * Configuration tree keys
     */
    const COLUMNS_KEY    = 'columns';
    const PROPERTIES_KEY = 'properties';

    /**
     * Configuration tree paths
     */
    const COLUMNS_PATH    = '[columns]';
    const PROPERTIES_PATH = '[properties]';

    /** @var PropertyInterface[] */
    protected $properties;

    /**
     * {@inheritDoc}
     */
    public function isApplicable(array $config)
    {
        $columns    = $this->accessor->getValue($config, self::COLUMNS_PATH) ? : array();
        $properties = $this->accessor->getValue($config, self::PROPERTIES_PATH) ? : array();
        $applicable = $columns || $properties;

        $columnTypes = array();
        foreach ($columns as $column) {
            if (isset($column['type'])) {
                $columnTypes[] = $column['type'];
            }
        }

        // validate extension configuration
        $this->validateConfiguration(
            new Configuration(array_merge(array_keys($this->properties), $columnTypes)),
            array(
                'columns_and_properties' => array_merge($columns, $properties)
            )
        );

        return $applicable;
    }

    /**
     * {@inheritDoc}
     */
    public function visitResult(array $config, \stdClass $result)
    {
        $rows       = (array)$result->rows;
        $results    = array();
        $columns    = $this->accessor->getValue($config, self::COLUMNS_PATH) ? : array();
        $properties = $this->accessor->getValue($config, self::PROPERTIES_PATH) ? : array();
        $toProcess  = array_merge($columns, $properties);

        foreach ($rows as $row) {
            $resultRecord = array();
            $record       = new ResultRecord($row);

            foreach ($toProcess as $name => $fieldConfig) {
                $property            = $this->getPropertyObject($name, $fieldConfig);
                $resultRecord[$name] = $property->getValue($record);
            }

            $results[] = array_merge($row, $resultRecord);
        }

        $result->rows = $results;
    }

    /**
     * {@inheritDoc}
     */
    public function visitMetadata(array $config, \stdClass $result)
    {
        // TODO: Implement visitMetadata() method.
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
     * @param string $name
     * @param array  $config
     *
     * @throws \RuntimeException
     * @return PropertyInterface
     */
    protected function getPropertyObject($name, array $config)
    {
        $config['name'] = $name;
        $config['type'] = $propertyType = $this->accessor->getValue($config, '[type]') ? : 'field';

        if (!$property = $this->accessor->getValue($this->properties, "[$propertyType]")) {
            throw new \RuntimeException(sprintf('Property type "%s" not found', $propertyType));
        }
        $property->init($config);

        return $property;
    }
}
