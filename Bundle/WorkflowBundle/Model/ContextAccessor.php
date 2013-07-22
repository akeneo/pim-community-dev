<?php

namespace Oro\Bundle\WorkflowBundle\Model;

use Symfony\Component\PropertyAccess\PropertyPath;
use Symfony\Component\PropertyAccess\PropertyAccess;
use Symfony\Component\PropertyAccess\PropertyAccessor;

use Symfony\Component\PropertyAccess\Exception\NoSuchPropertyException;

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
        if ($value instanceof PropertyPath) {
            try {
                return $this->getPropertyAccessor()->getValue($context, $value);
            } catch (NoSuchPropertyException $e) {
                return null;
            }

        } else {
            return $value;
        }
    }

    /**
     * Get PropertyAccessor
     *
     * @return PropertyAccessor
     */
    protected function getPropertyAccessor()
    {
        if (!$this->propertyAccessor) {
            $this->propertyAccessor = PropertyAccess::createPropertyAccessorBuilder()->getPropertyAccessor();
        }
        return $this->propertyAccessor;
    }
}
