<?php

namespace Akeneo\Tool\Component\StorageUtils\Repository;

/**
 * Interface to find one object by identifier (from cache if already fetched)
 *
 * @author    Nicolas Dupont <nicolas@akeneo.com>
 * @copyright 2015 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class BaseCachedObjectRepository implements CachedObjectRepositoryInterface
{
    /** @var IdentifiableObjectRepositoryInterface*/
    protected $repository;

    /** @var array */
    protected $objectsCache;

    /**
     * @param IdentifiableObjectRepositoryInterface $repository
     */
    public function __construct(IdentifiableObjectRepositoryInterface $repository)
    {
        $this->repository = $repository;
        $this->objectsCache = [];
    }

    /**
     * {@inheritdoc}
     */
    public function findOneByIdentifier($identifier)
    {
        if (!array_key_exists($identifier, $this->objectsCache)) {
            $this->objectsCache[$identifier] = $this->repository->findOneByIdentifier($identifier);
        }

        return $this->objectsCache[$identifier];
    }

    /**
     * {@inheritdoc}
     */
    public function getIdentifierProperties()
    {
        return $this->repository->getIdentifierProperties();
    }

    /**
     * {@inheritdoc}
     */
    public function clear()
    {
        $this->objectsCache = [];

        return $this;
    }
}
