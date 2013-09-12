<?php

namespace Oro\Bundle\InstallerBundle\Requirement;

use IteratorAggregate;
use ArrayIterator;

class OroRequirements implements IteratorAggregate
{
    protected $collections = array();

    public function __construct(array $requirementCollections)
    {
        foreach ($requirementCollections as $requirementCollection) {
            $this->add($requirementCollection);
        }
    }

    public function getIterator()
    {
        return new ArrayIterator($this->collections);
    }

    public function add(RequirementCollection $collection)
    {
        $this->collections[] = $collection;

        return $this;
    }
}
