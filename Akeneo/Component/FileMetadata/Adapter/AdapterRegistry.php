<?php

/**
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2015 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Akeneo\Component\FileMetadata\Adapter;

use Akeneo\Component\FileMetadata\Exception\AlreadyRegisteredAdapterException;
use Akeneo\Component\FileMetadata\Exception\NonRegisteredAdapterException;

/**
 * Registry for Adapters.
 *
 * @author    Julien Janvier <julien.janvier@akeneo.com>
 * @copyright 2015 Akeneo SAS (http://www.akeneo.com)
 */
class AdapterRegistry
{
    /** @var AdapterInterface[] */
    protected $adapters = [];

    /**
     * Return all Adapters.
     *
     * @return AdapterInterface[]
     */
    public function all()
    {
        return $this->adapters;
    }

    /**
     * @param AdapterInterface $adapter
     *
     * @throws AlreadyRegisteredAdapterException
     *
     * @return AdapterRegistry
     */
    public function add(AdapterInterface $adapter)
    {
        $name = $adapter->getName();
        if ($this->has($name)) {
            throw new AlreadyRegisteredAdapterException(sprintf('Adapter "%s" already registered.', $name));
        }

        $this->adapters[$name] = $adapter;

        return $this;
    }

    /**
     * @param string $name
     *
     * @throws NonRegisteredAdapterException
     *
     * @return AdapterInterface
     */
    public function get($name)
    {
        if ($this->has($name)) {
            return $this->adapters[$name];
        }

        throw new NonRegisteredAdapterException(sprintf('No "%s" adapter found.', $name));
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
