<?php

namespace Oro\Bundle\DataGridBundle\Extension\Formatter;

use Oro\Bundle\DataGridBundle\Extension\AbstractExtension;
use Oro\Bundle\DataGridBundle\Extension\Formatter\Property\PropertyInterface;

class FormatterExtension extends AbstractExtension
{
    /**
     * Configuration tree paths
     */
    const COLUMNS_PATH    = '[columns]';
    const PROPERTIES_PATH = '[properties]';

    /** @var PropertyInterface[] */
    protected $properties = [];

    /**
     * {@inheritDoc}
     */
    public function isApplicable(array $config)
    {
        $columns    = $this->accessor->getValue($config, self::COLUMNS_PATH) ? : [];
        $properties = $this->accessor->getValue($config, self::PROPERTIES_PATH) ? : [];
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
        $columns    = $this->accessor->getValue($config, self::COLUMNS_PATH) ? : [];
        $properties = $this->accessor->getValue($config, self::PROPERTIES_PATH) ? : [];
        $toProcess  = array_merge($columns, $properties);

        foreach ($rows as $row) {
            $resultRecord = [];
            $record       = new ResultRecord($row);

            foreach ($toProcess as $name => $fieldConfig) {
                $property            = $this->getPropertyObject($name, $fieldConfig);
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
        $data->columns = isset($data->columns) ? $data->columns : [];
        $columns         = $this->accessor->getValue($config, self::COLUMNS_PATH) ? : [];
        $columns         = array_filter(
            $columns,
            function ($item) {
                return strpos($item[PropertyInterface::TYPE_KEY], 'field') !== false;
            }
        );

        $data->columns = array_map(
            function ($item) {
                return array_intersect_key(
                    $item,
                    array_flip([PropertyInterface::TYPE_KEY, PropertyInterface::FRONTEND_OPTIONS_KEY])
                );
            },
            $columns
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

        $type = $this->accessor->getValue($config, sprintf('[%s]', PropertyInterface::TYPE_KEY));

        $property = $this->properties[$type];
        $property->init($config);

        return $property;
    }
}
