<?php

namespace PimEnterprise\Bundle\WorkflowBundle\Proposition;

/**
 * Store product value changes
 *
 * @author    Gildas Quemener <gildas@akeneo.com>
 * @copyright 2014 Akeneo SAS (http://www.akeneo.com)
 */
class ChangesCollector
{
    /** @var array */
    protected $data;

    public function setData(array $data)
    {
        $this->data = $data;
    }

    public function getData()
    {
        return $this->data;
    }
}
