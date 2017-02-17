<?php

namespace Akeneo\Bundle\StorageUtilsBundle\Doctrine;

use Doctrine\Common\Persistence\ManagerRegistry;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Get the table name from the entity parameter name
 * Ease overriding entities managing with DBAL support avoiding hard-coded table names
 *
 * @author    Romain Monceau <romain@akeneo.com>
 * @copyright 2014 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class TableNameBuilder
{
    /** @var ContainerInterface */
    protected $container;

    /** @var ManagerRegistry */
    protected $managerRegistry;

    /**
     * Construct
     *
     * @param ContainerInterface $container
     * @param ManagerRegistry    $managerRegistry
     */
    public function __construct(ContainerInterface $container, ManagerRegistry $managerRegistry)
    {
        $this->container = $container;
        $this->managerRegistry = $managerRegistry;
    }

    /**
     * Get table name from container parameter defined
     *
     * @param string $entityParameter
     * @param mixed  $targetEntity
     *
     * @return string
     */
    public function getTableName($entityParameter, $targetEntity = null)
    {
        $classMetadata = $this->getClassMetadata($entityParameter);

        if (null !== $targetEntity) {
            $assocMapping = $classMetadata->getAssociationMapping($targetEntity);

            return $assocMapping['joinTable']['name'];
        }

        return $classMetadata->getTableName();
    }

    /**
     * Returns class metadata for a defined entity parameter
     *
     * @param string $entityParameter
     *
     * @return \Doctrine\Common\Persistence\Mapping\ClassMetadata
     */
    protected function getClassMetadata($entityParameter)
    {
        $entityClassName = $this->container->getParameter($entityParameter);
        $manager = $this->managerRegistry->getManagerForClass($entityClassName);
        $classMetadata = $manager->getClassMetadata($entityClassName);

        return $classMetadata;
    }
}
