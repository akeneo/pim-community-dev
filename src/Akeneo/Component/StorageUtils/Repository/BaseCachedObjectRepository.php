<?php

namespace Akeneo\Component\StorageUtils\Repository;

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

    /** @var IdentifiableObjectsRepositoryInterface */
    protected $repository2;

    /** @var array */
    protected $objectsCache;

    /**
     * @param IdentifiableObjectRepositoryInterface $repository
     * @param IdentifiableObjectsRepositoryInterface $repository2
     */
    public function __construct(IdentifiableObjectRepositoryInterface $repository, IdentifiableObjectsRepositoryInterface $repository2 = null)
    {
        $this->repository = $repository;
        $this->repository2 = $repository2;
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

    public function findSeveralByIdentifiers(array $identifiers)
    {
        $identifiersNotCached = [];
        $objects = [];

        foreach ($identifiers as $identifier) {
            if (!array_key_exists($identifier, $this->objectsCache)) {
                $identifiersNotCached[] = $identifier;
            } else {
                $objects[$identifier] = $this->objectsCache[$identifier];
            }
        }

        $objectsNotCached = $this->repository2->findSeveralByIdentifiers($identifiersNotCached);
        foreach ($objectsNotCached as $object) {
            $objects[$object->getCode()] = $object;
            $this->objectsCache[$object->getCode()] = $object;
        }

        return $objects;
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
