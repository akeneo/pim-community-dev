<?php

namespace Akeneo\Test\Acceptance\Locale;

use Akeneo\Component\StorageUtils\Saver\SaverInterface;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Pim\Component\Catalog\Model\ChannelInterface;
use Pim\Component\Catalog\Model\LocaleInterface;
use Pim\Component\Catalog\Repository\LocaleRepositoryInterface;

class InMemoryLocaleRepository implements LocaleRepositoryInterface, SaverInterface
{
    /** @var Collection */
    private $locales;

    public function __construct()
    {
        $this->locales = new ArrayCollection();
    }

    public function getIdentifierProperties()
    {
        return ['code'];
    }

    public function findOneByIdentifier($code)
    {
        return $this->locales->get($code);
    }

    public function save($currency, array $options = [])
    {
        $this->locales->set($currency->getCode(), $currency);
    }

    /**
     * Return an array of activated locales
     *
     * @return LocaleInterface[]
     */
    public function getActivatedLocales()
    {
        // TODO: Implement getActivatedLocales() method.
    }

    /**
     * Return an array of activated locales codes
     *
     * @return array
     */
    public function getActivatedLocaleCodes()
    {
        // TODO: Implement getActivatedLocaleCodes() method.
    }

    /**
     * Return a query builder for activated locales
     *
     * @return mixed
     */
    public function getActivatedLocalesQB()
    {
        // TODO: Implement getActivatedLocalesQB() method.
    }

    /**
     * Get the deleted locales of a channel (the channel is updated but not flushed yet).
     *
     * @param ChannelInterface $channel
     *
     * @return array the list of deleted locales
     */
    public function getDeletedLocalesForChannel(ChannelInterface $channel)
    {
        // TODO: Implement getDeletedLocalesForChannel() method.
    }

    /**
     * Return the number of activated locales
     *
     * @return int
     */
    public function countAllActivated()
    {
        // TODO: Implement countAllActivated() method.
    }

    /**
     * Finds an object by its primary key / identifier.
     *
     * @param mixed $id The identifier.
     *
     * @return object|null The object.
     */
    public function find($id)
    {
        // TODO: Implement find() method.
    }

    /**
     * Finds all objects in the repository.
     *
     * @return array The objects.
     */
    public function findAll()
    {
        // TODO: Implement findAll() method.
    }

    /**
     * Finds objects by a set of criteria.
     *
     * Optionally sorting and limiting details can be passed. An implementation may throw
     * an UnexpectedValueException if certain values of the sorting or limiting details are
     * not supported.
     *
     * @param array      $criteria
     * @param array|null $orderBy
     * @param int|null   $limit
     * @param int|null   $offset
     *
     * @return array The objects.
     *
     * @throws \UnexpectedValueException
     */
    public function findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
    {
        // TODO: Implement findBy() method.
    }

    /**
     * Finds a single object by a set of criteria.
     *
     * @param array $criteria The criteria.
     *
     * @return object|null The object.
     */
    public function findOneBy(array $criteria)
    {
        // TODO: Implement findOneBy() method.
    }

    /**
     * Returns the class name of the object managed by the repository.
     *
     * @return string
     */
    public function getClassName()
    {
        // TODO: Implement getClassName() method.
    }
}
