<?php

namespace Oro\Bundle\DataGridBundle\Extension\Formatter\Property;

use Oro\Bundle\DataGridBundle\Extension\Formatter\ResultRecord;

abstract class AbstractProperty implements PropertyInterface
{
    /**
     * @var string
     */
    protected $name;

    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return $this->name;
    }
}
