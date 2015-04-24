<?php

namespace Akeneo\DAM\Component\Metadata\Adapter;

class AdapterRegistry
{
    /** @var AdapterInterface[] */
    protected $adapters = [];

    public function all()
    {
        return $this->adapters;
    }

    public function add(AdapterInterface $adapter)
    {
        $this->adapters[] = $adapter;

        return $this;
    }

    public function remove(AdapterInterface $adapter)
    {
        //TODO
    }
}
