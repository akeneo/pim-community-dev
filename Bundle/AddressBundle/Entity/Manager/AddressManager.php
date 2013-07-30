<?php

namespace Oro\Bundle\AddressBundle\Entity\Manager;

use Doctrine\ORM\Tools\Pagination\Paginator;
use Oro\Bundle\AddressBundle\Entity\AbstractAddress;
use Oro\Bundle\FlexibleEntityBundle\Entity\Repository\FlexibleEntityRepository;
use Oro\Bundle\FlexibleEntityBundle\Manager\FlexibleManager;

use Doctrine\Common\Persistence\ObjectManager;

/***
 * Class AddressManager
 * @package Oro\Bundle\AddressBundle\Entity\Manager
 *
 * @method string getFlexibleName()
 * @method string getFlexibleValueName()
 */
class AddressManager implements StorageInterface
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
     * @var FlexibleManager
     */
    protected $fm;

    /**
     * Constructor
     *
     * @param string          $class Entity name
     * @param ObjectManager   $om    Object manager
     * @param FlexibleManager $fm    Proxy for methods of flexible manager
     */
    public function __construct($class, ObjectManager $om, $fm)
    {
        $metadata = $om->getClassMetadata($class);

        $this->class = $metadata->getName();
        $this->om = $om;
        $this->fm = $fm;
    }

    /**
     * Returns an empty address instance
     *
     * @return \Oro\Bundle\AddressBundle\Entity\AbstractAddress
     */
    public function createAddress()
    {
        $class = $this->getClass();

        return new $class;
    }

    /**
     * Updates an address
     *
     * @param  AbstractAddress       $address
     * @param  bool              $flush   Whether to flush the changes (default true)
     * @throws \RuntimeException
     */
    public function updateAddress(AbstractAddress $address, $flush = true)
    {
        $this->getStorageManager()->persist($address);
        if ($flush) {
            $this->getStorageManager()->flush();
        }
    }

    /**
     * Deletes an address
     *
     * @param AbstractAddress $address
     */
    public function deleteAddress(AbstractAddress $address)
    {
        $this->getStorageManager()->remove($address);
        $this->getStorageManager()->flush();
    }

    /**
     * Finds one address by the given criteria
     *
     * @param  array       $criteria
     * @return AbstractAddress
     */
    public function findAddressBy(array $criteria)
    {
        return $this->getRepository()->findOneBy($criteria);
    }

    /**
     * Reloads an address
     *
     * @param AbstractAddress $address
     */
    public function reloadAddress(AbstractAddress $address)
    {
        $this->getStorageManager()->refresh($address);
    }

    /**
     * Returns the address's fully qualified class name.
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

    /**
     * Returns basic query instance to get collection with all user instances
     *
     * @param  int       $limit
     * @param  int       $offset
     * @return Paginator
     */
    public function getListQuery($limit = 10, $offset = 1)
    {
        /** @var FlexibleEntityRepository $repository */
        $repository = $this->fm->getFlexibleRepository();

        return $repository->findByWithAttributesQB(array(), null, array('id' => 'ASC'), $limit, $offset);
    }

    /**
     * Provide proxy method calls to flexible manager
     *
     * @param  string            $name
     * @param  array             $args
     * @return mixed
     * @throws \RuntimeException
     */
    public function __call($name, $args)
    {
        if (method_exists($this->fm, $name)) {
            return call_user_func_array(array($this->fm, $name), $args);
        }

        throw new \RuntimeException(sprintf('Unknown method "%s"', $name));
    }
}
