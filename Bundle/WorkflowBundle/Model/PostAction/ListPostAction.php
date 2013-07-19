<?php

namespace Oro\Bundle\WorkflowBundle\Model\PostAction;

use Oro\Bundle\WorkflowBundle\Entity\WorkflowItem;

class ListPostAction implements PostActionInterface
{
    const ALIAS = 'list';

    /**
     * @var PostActionInterface[]
     */
    protected $postActions;

    /**
     * @param PostActionInterface $postAction
     * @return ListPostAction
     */
    public function addPostAction(PostActionInterface $postAction)
    {
        $this->postActions[] = $postAction;

        return $this;
    }

    /**
     * {@inheritDoc}
     */
    public function execute(WorkflowItem $workflowItem)
    {
        foreach ($this->postActions as $postAction) {
            $postAction->execute($workflowItem);
        }
    }
}
