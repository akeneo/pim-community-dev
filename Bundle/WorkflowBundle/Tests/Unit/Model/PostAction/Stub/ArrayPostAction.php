<?php

namespace Oro\Bundle\WorkflowBundle\Tests\Unit\Model\PostAction\Stub;

use Doctrine\Common\Collections\ArrayCollection;

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
     * @param string $name
     * @param mixed $value
     */
    public function __set($name, $value)
    {
        $this->set($name, $value);
    }
}
