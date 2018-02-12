<?php

namespace Akeneo\Test\Acceptance\Channel;

use Akeneo\Component\StorageUtils\Saver\SaverInterface;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Pim\Component\Catalog\Model\ChannelInterface;
use Pim\Component\Catalog\Model\CurrencyInterface;
use Pim\Component\Catalog\Repository\ChannelRepositoryInterface;

class InMemoryChannelRepository implements ChannelRepositoryInterface, SaverInterface
{
    /** @var Collection */
    private $channels;

    public function __construct()
    {
        $this->channels = new ArrayCollection();
    }

    public function getIdentifierProperties()
    {
        return ['code'];
    }

    public function findOneByIdentifier($code)
    {
        return $this->channels->get($code);
    }

    public function save($channel, array $options = [])
    {
        $this->channels->set($channel->getCode(), $channel);
    }

    /**
     * Return the number of existing channels
     *
     * @return int
     */
    public function countAll()
    {
        // TODO: Implement countAll() method.
    }

    /**
     * Return an array of channel codes
     *
     * @return array
     */
    public function getChannelCodes()
    {
        // TODO: Implement getChannelCodes() method.
    }

    /**
     * Get full channels with locales and currencies
     *
     * @return ChannelInterface[]
     */
    public function getFullChannels()
    {
        // TODO: Implement getFullChannels() method.
    }

    /**
     * Get channels count for the given currency
     *
     * @param CurrencyInterface $currency
     *
     * @return int
     */
    public function getChannelCountUsingCurrency(CurrencyInterface $currency)
    {
        // TODO: Implement getChannelCountUsingCurrency() method.
    }

    /**
     * Get channel choices
     * Allow to list channels in an array like array[<code>] = <label>
     *
     * @param string $localeCode
     *
     * @return string[]
     */
    public function getLabelsIndexedByCode($localeCode)
    {
        // TODO: Implement getLabelsIndexedByCode() method.
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
