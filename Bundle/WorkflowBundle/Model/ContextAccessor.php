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
     * Checks whether context has value
     *
     * @param mixed $context
     * @param mixed $value
     * @return bool
     */
    public function hasValue($context, $value)
    {
        if ($value instanceof PropertyPath) {
            try {
                $key = $value->getElement($value->getLength() - 1);
                $parentValue = $this->getPropertyAccessor()->getValue($context, $value->getParent());
                if (is_array($parentValue)) {
                    return array_key_exists($key, $parentValue);
                } elseif ($parentValue instanceof \ArrayAccess) {
                    return isset($parentValue[$key]);
                } else {
                    return $this->getPropertyAccessor()->getValue($context, $value) !== null;
                }
            } catch (\Exception $e) {
                return false;
            }
        } else {
            return false;
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
