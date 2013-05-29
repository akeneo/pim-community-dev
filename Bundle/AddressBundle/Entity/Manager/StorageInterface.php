<?php

namespace Oro\Bundle\AddressBundle\Entity\Manager;

use Oro\Bundle\AddressBundle\Entity\AddressBase;

interface StorageInterface
{
    /**
     * Returns an empty address instance
     *
     * @return AddressBase
     */
    public function createAddress();

    /**
     * Updates an address
     *
     * @param  AddressBase       $address
     * @param  bool              $flush   Whether to flush the changes (default true)
     * @throws \RuntimeException
     */
    public function updateAddress(AddressBase $address, $flush = true);

    /**
     * Deletes an address
     *
     * @param AddressBase $address
     */
    public function deleteAddress(AddressBase $address);

    /**
     * Finds one address by the given criteria
     *
     * @param  array       $criteria
     * @return AddressBase
     */
    public function findAddressBy(array $criteria);

    /**
     * Reloads an address
     *
     * @param AddressBase $address
     */
    public function reloadAddress(AddressBase $address);

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
