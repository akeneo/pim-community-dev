<?php

namespace Oro\Bundle\WorkflowBundle\Model\Condition;

use Doctrine\Common\Persistence\ManagerRegistry;

use Oro\Bundle\WorkflowBundle\Model\ContextAccessor;

class EqualTo extends AbstractComparison
{
    /**
     * @var ManagerRegistry
     */
    protected $registry;

    /**
     * Constructor
     *
     * @param ManagerRegistry $registry
     * @param ContextAccessor $contextAccessor
     */
    public function __construct(ManagerRegistry $registry, ContextAccessor $contextAccessor)
    {
        $this->registry = $registry;
        parent::__construct($contextAccessor);
    }

    /**
     * Compare two values for equality
     *
     * @param mixed $left
     * @param mixed $right
     * @return boolean
     */
    protected function doCompare($left, $right)
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
}
