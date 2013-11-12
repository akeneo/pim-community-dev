<?php

namespace Oro\Bundle\WorkflowBundle\Model;

use Symfony\Component\PropertyAccess\PropertyPath;
use Symfony\Component\PropertyAccess\PropertyAccess;
use Symfony\Component\PropertyAccess\PropertyAccessor;
use Symfony\Component\PropertyAccess\PropertyPathInterface;

class ContextAccessor
{
    /**
     * @var PropertyAccessor
     */
    protected $propertyAccessor;

    /**
     * Set value to context.
     *
     * @param object|array $context
     * @param string|PropertyPathInterface $property
     * @param mixed $value
     */
    public function setValue($context, $property, $value)
    {
        $this->getPropertyAccessor()->setValue(
            $context,
            $property,
            $this->getValue($context, $value)
        );
    }

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
            } catch (\Exception $e) {
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
            $this->propertyAccessor = PropertyAccess::createPropertyAccessor();
        }
        return $this->propertyAccessor;
    }
}
