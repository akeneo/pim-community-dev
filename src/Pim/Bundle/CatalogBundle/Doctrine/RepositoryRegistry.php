<?php

namespace Pim\Bundle\CatalogBundle\Doctrine;

use Doctrine\Common\Persistence\ObjectRepository;

/**
 * Repository registry
 *
 * @author    Romain Monceau <romain@akeneo.com>
 * @copyright 2014 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class RepositoryRegistry
{
    /** @var ObjectRepository[] */
    protected $repositories = array();

    /**
     * Add repository service in registry
     *
     * @param string           $entityClass
     * @param ObjectRepository $repository
     *
     * @throws \LogicException
     */
    public function addRepository($entityClass, ObjectRepository $repository)
    {
        if (isset($this->repositories[$entityClass])) {
            throw new \LogicException(
                sprintf(
                    '"%s" entity class is already defined for repository service "%"',
                    $entityClass,
                    get_class($repository)
                )
            );
        }

        $this->repositories[$entityClass] = $repository;
    }

    /**
     * Get repository
     *
     * @param string $entityClass
     *
     * @return ObjectRepository
     *
     * @throws \LogicException
     */
    public function getRepository($entityClass)
    {
        if (!isset($this->repositories[$entityClass])) {
            throw new \LogicException(sprintf('Repository for "%s" is unknown', $entityClass));
        }

        return $this->repositories[$entityClass];
    }
}
