<?php

namespace Oro\Bundle\GridBundle\Property;

use Oro\Bundle\GridBundle\Datagrid\ResultRecord;

abstract class AbstractProperty implements PropertyInterface
{
    /**
     * @var string
     */
    protected $name;

    /**
     * @var array
     */
    protected $formatters = array();

    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * Set formatters array
     *
     * @param array $formatters
     */
    public function setFormatters($formatters)
    {
        $this->formatters = $formatters;
    }

    /**
     * Returns formatter for given type
     *
     * @param $type
     *
     * @return bool|FormatterInterface
     */
    public function getFormatterByType($type)
    {
        if (isset($this->formatters[$type])) {
            return $this->formatters[$type];
        }

        return false;
    }
}
