<?php

namespace Oro\Bundle\AddressBundle\Entity\Manager;

use Oro\Bundle\AddressBundle\Entity\AbstractAddress;

interface StorageInterface
{
    /**
     * Returns an empty address instance
     *
     * @return AbstractAddress
     */
    public function createAddress();

    /**
     * Updates an address
     *
     * @param AbstractAddress $address
     * @param bool $flush Whether to flush the changes (default true)
     * @throws \RuntimeException
     */
    public function updateAddress(AbstractAddress $address, $flush = true);

    /**
     * Deletes an address
     *
     * @param AbstractAddress $address
     */
    public function deleteAddress(AbstractAddress $address);

    /**
     * Finds one address by the given criteria
     *
     * @param array $criteria
     * @return AbstractAddress
     */
    public function findAddressBy(array $criteria);

    /**
     * Reloads an address
     *
     * @param AbstractAddress $address
     */
    public function reloadAddress(AbstractAddress $address);

    /**
     * Returns the address's fully qualified class name.
     *
     * @return string
     */
    public function getClass();

    /**
     * Return related repository
     *
     * @return \Doctrine\Common\Persistence\ObjectRepository
     */
    public function getRepository();
}
