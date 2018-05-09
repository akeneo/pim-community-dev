<?php

namespace Pim\Bundle\CatalogBundle\Doctrine\ORM\Repository;

use Akeneo\Component\StorageUtils\Repository\CachedObjectRepositoryInterface;
use Akeneo\Component\StorageUtils\Repository\IdentifiableManyObjectsRepositoryInterface;
use Pim\Component\Catalog\Repository\AttributeOptionRepositoryInterface;

/**
 * Class CachedAttributeOptionRepository
 *
 * @author    Alexandre Hocquard <alexandre.hocquard@akeneo.com>
 * @copyright 2017 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class CachedAttributeOptionRepository implements CachedObjectRepositoryInterface, IdentifiableManyObjectsRepositoryInterface
{
    /** @var AttributeOptionRepository*/
    protected $repository;

    /** @var array */
    protected $objectsCache;

    /**
     * @param AttributeOptionRepositoryInterface $repository
     */
    public function __construct(AttributeOptionRepositoryInterface $repository)
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
    public function findManyByIdentifier(array $codes)
    {
        $uncachedCodes = [];
        $cachedCodes = [];
        foreach ($codes as $code) {
            if (!array_key_exists($code, $this->objectsCache)) {
                $uncachedCodes[] = $code;
            } else {
                $cachedCodes[$code] = $this->objectsCache[$code];
            }
        }

        if (!empty($uncachedCodes)) {
            $attributeOptions = $this->repository->findManyByIdentifier($uncachedCodes);
            foreach ($attributeOptions as $attributeOption) {
                $key = $attributeOption->getAttribute()->getCode() . '.' . $attributeOption->getCode();
                $this->objectsCache[$key] = $attributeOption;
                $cachedCodes[$key] = $attributeOption;
            }
        }

        return $cachedCodes;
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
