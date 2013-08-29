<?php

namespace Oro\Bundle\WorkflowBundle\Model\Condition;

use Doctrine\Common\Persistence\ManagerRegistry;

use Oro\Bundle\WorkflowBundle\Model\ContextAccessor;
use Oro\Bundle\WorkflowBundle\Model\MetadataManager;

class EqualTo extends AbstractComparison
{
    /**
     * @var MetadataManager
     */
    protected $metadataManager;

    /**
     * @param MetadataManager $metadataManager
     * @param ContextAccessor $contextAccessor
     */
    public function __construct(ContextAccessor $contextAccessor, MetadataManager $metadataManager)
    {
        parent::__construct($contextAccessor);

        $this->metadataManager = $metadataManager;
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
            $leftClass = $this->metadataManager->getEntityClass($left);
            $rightClass = $this->metadataManager->getEntityClass($right);

            if ($leftClass == $rightClass
                && $this->metadataManager->isManageableEntity($left)
                && $this->metadataManager->isManageableEntity($right)
            ) {
                $leftIdentifier = $this->metadataManager->getEntityIdentifier($left);
                $rightIdentifier = $this->metadataManager->getEntityIdentifier($right);

                return $leftIdentifier == $rightIdentifier;
            }
        }

        return $left == $right;
    }
}
