<?php

namespace Oro\Bundle\EntityBundle\ORM;

use Doctrine\Common\EventManager;
use Doctrine\DBAL\Connection;

use Doctrine\ORM\Configuration;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\ORMException;

use Doctrine\ORM\ORMInvalidArgumentException;
use Oro\Bundle\EntityBundle\ORM\Query\FilterCollection;

use Oro\Bundle\EntityExtendBundle\Entity\ExtendProxyInterface;
use Oro\Bundle\EntityExtendBundle\Extend\ExtendManager;

class OroEntityManager extends EntityManager
{
    /**
     * Collection of query filters.
     *
     * @var FilterCollection
     */
    protected $filterCollection;

    /**
     * Manager for extend and custom entities
     *
     * @var ExtendManager
     */
    protected $extendManager;

    public static function create($conn, Configuration $config, EventManager $eventManager = null)
    {
        if (!$config->getMetadataDriverImpl()) {
            throw ORMException::missingMappingDriverImpl();
        }

        if (is_array($conn)) {
            $conn = \Doctrine\DBAL\DriverManager::getConnection($conn, $config, ($eventManager ? : new EventManager()));
        } elseif ($conn instanceof Connection) {
            if ($eventManager !== null && $conn->getEventManager() !== $eventManager) {
                throw ORMException::mismatchedEventManager();
            }
        } else {
            throw new \InvalidArgumentException("Invalid argument: " . $conn);
        }

        return new OroEntityManager($conn, $config, $conn->getEventManager());
    }

    /**
     * @param \Oro\Bundle\EntityExtendBundle\Extend\ExtendManager $extendManager
     * @return $this
     */
    public function setExtendManager($extendManager)
    {
        $this->extendManager = $extendManager;

        return $this;
    }

    /**
     * @return ExtendManager
     */
    public function getExtendManager()
    {
        return $this->extendManager;
    }

    /**
     * @param $entity
     * @return bool
     */
    public function isExtendEntity($entity)
    {
        return $this->extendManager->isExtend($entity);
    }

    /**
     * Get Proxy object for some entity
     *  param can be some entity object or array with include entity name and criteria
     *        array(
     *              'SomeBundle:Entity',
     *              1
     *          )
     *        or
     *        array(
     *              'SomeBundle:Entity',
     *              array(
     *                  'name' => 'someName'
     *              )
     *          )
     *
     * @param object|array $entity
     * @return ExtendProxyInterface
     */
    public function getExtendEntity($entity)
    {
        return $this->extendManager->loadExtendEntity($entity);
    }

    /**
     * {@inheritdoc}
     */
    public function persist($entity)
    {
        if ($entity instanceof ExtendProxyInterface) {
            parent::persist($entity->__proxy__getExtend());
        }

        parent::persist($entity);
    }

    /**
     * {@inheritdoc}
     */
    public function remove($entity)
    {
        if ($entity instanceof ExtendProxyInterface) {
            parent::remove($entity->__proxy__getExtend());
        }

        parent::remove($entity);
    }


    /**
     * @throws \Exception
     */
    public function getExtendRepository()
    {
        throw new \Exception("OroEntityManager::getExtendRepository is not implemented");
    }

    /**
     * @param FilterCollection $collection
     */
    public function setFilterCollection(FilterCollection $collection)
    {
        $this->filterCollection = $collection;
    }

    /**
     * Gets the enabled filters.
     *
     * @return FilterCollection The active filter collection.
     */
    public function getFilters()
    {
        if (null === $this->filterCollection) {
            $this->filterCollection = new FilterCollection($this);
        }

        return $this->filterCollection;
    }

    /**
     * Checks whether the state of the filter collection is clean.
     *
     * @return boolean True, if the filter collection is clean.
     */
    public function isFiltersStateClean()
    {
        return null === $this->filterCollection || $this->filterCollection->isClean();
    }

    /**
     * Checks whether the Entity Manager has filters.
     *
     * @return boolean True, if the EM has a filter collection with enabled filters.
     */
    public function hasFilters()
    {
        return null !== $this->filterCollection && $this->filterCollection->getEnabledFilters();
    }
}
