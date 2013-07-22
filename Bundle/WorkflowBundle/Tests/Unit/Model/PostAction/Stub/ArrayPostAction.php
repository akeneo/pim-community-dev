<?php

namespace Oro\Bundle\WorkflowBundle\Tests\Unit\Model\PostAction\Stub;

use Doctrine\Common\Collections\ArrayCollection;

use Oro\Bundle\WorkflowBundle\Exception\InvalidParameterException;
use Oro\Bundle\WorkflowBundle\Model\PostAction\PostActionInterface;

class ArrayPostAction extends ArrayCollection implements PostActionInterface
{
    /**
     * Do nothing
     *
     * @param mixed $context
     */
    public function execute($context)
    {
    }

    /**
     * Initialize post action based on passed options.
     *
     * @param array $options
     * @return PostActionInterface
     * @throws InvalidParameterException
     */
    public function initialize(array $options)
    {
        $this->set('parameters', $options);
        return $this;
    }
}
