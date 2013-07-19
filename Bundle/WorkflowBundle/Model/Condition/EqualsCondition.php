<?php

namespace Oro\Bundle\WorkflowBundle\Model\Condition;

use Doctrine\Common\Persistence\ManagerRegistry;
use Symfony\Component\PropertyAccess\PropertyPath;
use Symfony\Component\PropertyAccess\PropertyAccess;
use Symfony\Component\PropertyAccess\PropertyAccessor;

use Oro\Bundle\WorkflowBundle\Exception\ConditionOptionRequiredException;

class EqualsCondition implements ConditionInterface
{
    /**
     * @var string
     */
    protected $left;

    /**
     * @var string
     */
    protected $right;

    /**
     * @var ManagerRegistry
     */
    protected $registry;

    /**
     * @var PropertyAccessor
     */
    protected $propertyAccessor;

    /**
     * Constructor
     *
     * @param ManagerRegistry $registry
     */
    public function __construct(ManagerRegistry $registry)
    {
        $this->registry = $registry;
    }

    /**
     * Check if values equals.
     *
     * @param mixed $context
     * @return boolean
     */
    public function isAllowed($context)
    {
        return $this->isEquals($this->getValue($context, $this->left), $this->getValue($context, $this->right));
    }

    /**
     * Get value from context
     *
     * @param mixed $context
     * @param mixed $value
     * @return mixed
     */
    protected function getValue($context, $value)
    {
        return $value instanceof PropertyPath ? $this->getPropertyAccessor()->getValue($context, $this->left) : $value;
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

    /**
     * Compare two values for equality
     *
     * @param mixed $left
     * @param mixed $right
     * @return boolean
     */
    protected function isEquals($left, $right)
    {
        if ($left == $right) {
            return true;
        } elseif (is_object($left) && is_object($right)) {
            $leftClass = get_class($left);
            $rightClass = get_class($right);
            $leftManager = $this->registry->getManagerForClass(get_class($left));
            $rightManager = $this->registry->getManagerForClass(get_class($right));
            if ($leftManager && $rightManager) {
                $leftMetadata = $leftManager->getClassMetadata($leftClass);
                $rightMetadata = $rightManager->getClassMetadata($rightClass);
                if ($leftMetadata->getName() == $rightMetadata->getName()) {
                    return $leftMetadata->getIdentifierValues($left) == $rightMetadata->getIdentifierValues($right);
                }
            }
        }
        return false;
    }

    /**
     * Initialize condition options
     *
     * @param array $options
     * @return EqualsCondition
     * @throws ConditionOptionRequiredException If "left" or "right" option is empty
     */
    public function initialize(array $options)
    {
        if (isset($options['left'])) {
            $this->left = $options['left'];
        } else {
            throw new ConditionOptionRequiredException('left');
        }

        if (isset($options['right'])) {
            $this->right = $options['right'];
        } else {
            throw new ConditionOptionRequiredException('right');
        }

        return $this;
    }
}
