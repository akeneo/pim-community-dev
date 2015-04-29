<?php

namespace DamEnterprise\Component\Metadata\Adapter;

class AdapterRegistry
{
    /** @var AdapterInterface[] */
    protected $adapters = [];

    /**
     * @return AdapterInterface[]
     */
    public function all()
    {
        return $this->adapters;
    }

    /**
     * @param AdapterInterface $adapter
     *
     * @return AdapterRegistry
     */
    public function add(AdapterInterface $adapter)
    {
        $name = $adapter->getName();
        if ($this->has($name)) {
            throw new \LogicException(sprintf('Adapter "%s" already registered.', $name));
        }

        $this->adapters[$name] = $adapter;

        return $this;
    }

    /**
     * @param string $name
     *
     * @return AdapterInterface
     * @throws \LogicException
     */
    public function get($name)
    {
        if ($this->has($name)) {
            return $this->adapters[$name];
        }

        throw new \LogicException(sprintf('No "%s" adapter found.', $name));
    }

    /**
     * @param string $name
     *
     * @return bool
     */
    public function has($name)
    {
        return isset($this->adapters[$name]);
    }
}
