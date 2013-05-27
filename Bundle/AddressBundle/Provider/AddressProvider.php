<?php

namespace Oro\Bundle\AddressBundle\Provider;

use Oro\Bundle\AddressBundle\Entity\Manager\StorageInterface;

class AddressProvider
{
    const COMMON_STORAGE = '_common_storage';

    /**
     * @var array
     */
    private $storage= array();

    /**
     * Add storage to provider
     *
     * @param StorageInterface $storage
     * @param string $alias
     * @return $this
     */
    public function addStorage(StorageInterface $storage, $alias = self::COMMON_STORAGE)
    {
        $this->assertAlias($alias);

        $this->storage[$alias] = $storage;

        return $this;
    }

    /**
     * Returns storage by alias
     *
     * @param string $alias
     * @return null|StorageInterface
     */
    public function getStorage($alias = self::COMMON_STORAGE)
    {
        if ($this->has($alias)) {
            return $this->storage[$alias];
        }

        return null;
    }

    /**
     * Checks whether an address storage exists in this provider
     *
     * @param string $alias
     * @return boolean
     */
    private function has($alias)
    {
        $this->assertAlias($alias);

        return array_key_exists($alias, $this->storage);
    }

    /**
     * Assert alias not empty
     *
     * @param string $alias
     * @throws \InvalidArgumentException
     */
    private function assertAlias($alias)
    {
        if (empty($alias)) {
            throw new \InvalidArgumentException('Storage alias was not set.');
        }
    }
}
