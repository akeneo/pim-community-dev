<?php

namespace Oro\Bundle\InstallerBundle\Requirement;

use IteratorAggregate;
use ArrayIterator;

class RequirementCollection implements IteratorAggregate
{
    protected $label;
    protected $requirements = array();

    public function __construct($label)
    {
        $this->label = $label;
    }

    public function getLabel()
    {
        return $this->label;
    }

    public function getIterator()
    {
        return new ArrayIterator($this->requirements);
    }

    public function add(Requirement $requirement)
    {
        $this->requirements[] = $requirement;

        return $this;
    }
}
