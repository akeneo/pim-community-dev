<?php

namespace Oro\Bundle\WorkflowBundle\Model\Pass;

interface PassInterface
{
    /**
     * Pass through data, processes it and returns modified data
     *
     * @param array $data
     * @return array
     */
    public function pass(array $data);
}
