<?php

namespace Oro\Bundle\AddressBundle\Entity\Manager;

use Oro\Bundle\AddressBundle\Entity\AddressType;
use Doctrine\Common\Persistence\ObjectManager;

/***
 * Class AddressTypeManager
 * @package Oro\Bundle\AddressBundle\Entity\Manager
 *
 */
class AddressTypeManager
{
    /**
     * @var string
     */
    protected $class;

    /**
     * @var ObjectManager
     */
    protected $om;

    /**
     * Constructor
     *
     * @param string        $class Entity name
     * @param ObjectManager $om    Object manager
     */
    public function __construct($class, ObjectManager $om)
    {
        $metadata = $om->getClassMetadata($class);

        $this->class = $metadata->getName();
        $this->om = $om;
    }

    /**
     * Returns an empty address type instance
     *
     * @return AddressType
     */
    public function createAddressType()
    {
        $class = $this->getClass();

        return new $class;
    }

    /**
     * Updates an address type
     *
     * @param \Oro\Bundle\AddressBundle\Entity\AddressType
     * @param bool $flush Whether to flush the changes (default true)
     */
    public function updateAddressType(AddressType $addressType, $flush = true)
    {
        $this->getStorageManager()->persist($addressType);
        if ($flush) {
            $this->getStorageManager()->flush();
        }
    }

    /**
     * Deletes an address type
     *
     * @param AddressType $addressType
     */
    public function deleteAddressType(AddressType $addressType)
    {
        $this->getStorageManager()->remove($addressType);
        $this->getStorageManager()->flush();
    }

    /**
     * Finds one address type by the given criteria
     *
     * @param  array       $criteria
     * @return AddressType
     */
    public function findAddressTypeBy(array $criteria)
    {
        return $this->getRepository()->findOneBy($criteria);
    }

    /**
     * Reloads an address type
     *
     * @param AddressType $addressType
     */
    public function reloadAddressType(AddressType $addressType)
    {
        $this->getStorageManager()->refresh($addressType);
    }

    /**
     * Returns the address's type fully qualified class name.
     *
     * @return string
     */
    public function getClass()
    {
        return $this->class;
    }

    /**
     * Return related repository
     *
     * @return \Doctrine\Common\Persistence\ObjectRepository
     */
    public function getRepository()
    {
        return $this->getStorageManager()->getRepository($this->getClass());
    }

    /**
     * Retrieve object manager
     *
     * @return \Doctrine\Common\Persistence\ObjectManager
     */
    public function getStorageManager()
    {
        return $this->om;
    }
}
