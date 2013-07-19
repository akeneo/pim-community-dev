<?php

namespace Oro\Bundle\WorkflowBundle;

use Symfony\Component\PropertyAccess\PropertyPath;
use Symfony\Component\PropertyAccess\PropertyAccess;
use Symfony\Component\PropertyAccess\PropertyAccessor;

class ContextAccessor
{
    /**
     * @var PropertyAccessor
     */
    protected $propertyAccessor;

    /**
     * Get value from context
     *
     * @param mixed $context
     * @param mixed $value
     * @return mixed
     */
    public function getValue($context, $value)
    {
        return $value instanceof PropertyPath ? $this->getPropertyAccessor()->getValue($context, $value) : $value;
    }

    /**
     * Get PropertyAccessor
     *
     * @return PropertyAccessor
     */
    protected function getPropertyAccessor()
    {
        if ($this->propertyAccessor) {
            $this->propertyAccessor = PropertyAccess::createPropertyAccessorBuilder()->getPropertyAccessor();
        }
        return $this->propertyAccessor;
    }
}
