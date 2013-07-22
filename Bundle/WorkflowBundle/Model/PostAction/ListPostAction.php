<?php

namespace Oro\Bundle\WorkflowBundle\Model\PostAction;

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
    public function execute($context)
    {
        foreach ($this->postActions as $postAction) {
            $postAction->execute($context);
        }
    }
}
