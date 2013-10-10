<?php

namespace Oro\Bundle\DataGridBundle\Extension\Formatter;

use Oro\Bundle\DataGridBundle\Datasource\DatasourceInterface;
use Oro\Bundle\DataGridBundle\Extension\ExtensionVisitorInterface;
use Oro\Bundle\DataGridBundle\Extension\Formatter\Property\FieldProperty;
use Oro\Bundle\DataGridBundle\Extension\Formatter\Property\PropertyInterface;

class FormatterExtension implements ExtensionVisitorInterface
{
    /**
     * {@inheritDoc}
     */
    public function isApplicable(array $config)
    {
        return (!empty($config['columns']) || !empty($config['properties']));
    }

    /**
     * {@inheritDoc}
     */
    public function visitDatasource(DatasourceInterface $datasource)
    {
        // this extension do not affect source, do nothing
    }

    /**
     * {@inheritDoc}
     */
    public function visitResult(array $config, \stdClass $result)
    {
        $rows    = (array)$result->rows;
        $results = array();

        foreach ($rows as $row) {
            $result = array();
            $row    = new ResultRecord($row);

            foreach ($row as $name => $field) {

                $fieldConfig   = isset($config['columns'][$name]) ? $config['columns'][$name] : array();
                $property      = $this->getPropertyObject($name, $fieldConfig);
                $result[$name] = $property->getValue($row);
            }
        }
    }

    /**
     * @param string $name
     * @param array  $config
     *
     * @return PropertyInterface
     */
    protected function getPropertyObject($name, array $config)
    {
        return new FieldProperty();
    }
}
