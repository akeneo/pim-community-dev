<?php

namespace Oro\Bundle\WorkflowBundle\Model\Condition;

use Doctrine\Common\Persistence\ManagerRegistry;

use Oro\Bundle\WorkflowBundle\Model\ContextAccessor;
use Oro\Bundle\WorkflowBundle\Model\DoctrineHelper;

class EqualTo extends AbstractComparison
{
    /**
     * @var DoctrineHelper
     */
    protected $doctrineHelper;

    /**
     * @param DoctrineHelper $doctrineHelper
     * @param ContextAccessor $contextAccessor
     */
    public function __construct(ContextAccessor $contextAccessor, DoctrineHelper $doctrineHelper)
    {
        parent::__construct($contextAccessor);

        $this->doctrineHelper = $doctrineHelper;
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
        if (is_object($left) && is_object($right)) {
            $leftClass = $this->doctrineHelper->getEntityClass($left);
            $rightClass = $this->doctrineHelper->getEntityClass($right);

            if ($leftClass == $rightClass
                && $this->doctrineHelper->isManageableEntity($left)
                && $this->doctrineHelper->isManageableEntity($right)
            ) {
                $leftIdentifier = $this->doctrineHelper->getEntityIdentifier($left);
                $rightIdentifier = $this->doctrineHelper->getEntityIdentifier($right);

                return $leftIdentifier == $rightIdentifier;
            }
        }

        return $left == $right;
    }
}
