<?php

namespace Pim\Bundle\CatalogBundle\Doctrine;

use Doctrine\ORM\EntityManagerInterface;

/**
 * Override of repository factory
 * Repository factory returns only service instead of instanciate repositories
 *
 * @author    Romain Monceau <romain@akeneo.com>
 * @copyright 2014 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class RepositoryFactory extends DefaultRepositoryFactory
{
    /**
     * @var RepositoryRegistry
     */
    protected $repositoryRegistry;

    /**
     * Construct
     *
     * @param RepositoryRegistry $repositoryRegistry
     */
    public function __construct(RepositoryRegistry $repositoryRegistry)
    {
        $this->repositoryRegistry = $repositoryRegistry;
    }

    /**
     *
     * @param EntityManagerInterface $entityManager
     * @param string $entityName
     *
     * @return Doctrine\Common\Persistence\ObjectRepository
     */
    public function createRepository(EntityManagerInterface $entityManager, $entityName)
    {
        return $this->repositoryRegistry->getRepository($entityName);
    }
}
