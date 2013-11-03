<?php

namespace Oro\Bundle\GridBundle\Property;

use Oro\Bundle\GridBundle\Datagrid\ResultRecordInterface;

/**
 * Class ActionConfigurationProperty
 * @package Oro\Bundle\GridBundle\Property
 */
class ActionConfigurationProperty extends AbstractProperty
{
    const PROPERTY_NAME = 'action_configuration';

    /**
     * @param callback $callback
     */
    public function __construct($callback)
    {
        $this->callback = $callback;
    }

    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return self::PROPERTY_NAME;
    }

    /**
     * {@inheritdoc}
     */
    public function getValue(ResultRecordInterface $record)
    {
        $result = call_user_func($this->callback, $record);

        return is_array($result) ? $result : array();
    }
}
