<?php

namespace Oro\Bundle\WorkflowBundle\Tests\Unit\Model\Stub;

class BoundEntity
{
    /**
     * @var int
     */
    public $id;

    /**
     * @param int|null $id
     */
    public function __construct($id = null)
    {
        $this->id = $id;
    }
}
