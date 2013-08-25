<?php

namespace Oro\Bundle\EntityBundle\ORM;

use Doctrine\ORM\EntityManager;

/**
 * This class allows to get the real class name of an entity by its name
 */
class EntityClassResolver
{
    /**
     * @var EntityManager
     */
    protected $em;

    /**
     * Constructor
     *
     * @param EntityManager $em
     */
    public function __construct(EntityManager $em)
    {
        $this->em = $em;
    }

    /**
     * Gets the full class name for the given entity
     *
     * @param string $entityName The name of the entity. Can be bundle:entity or full class name
     * @return string The full class name
     * @throws \InvalidArgumentException
     */
    public function getEntityClass($entityName)
    {
        $split = explode(':', $entityName);
        if (count($split) <= 1) {
            // The given entity name is not in bundle:entity format. Suppose that it is the full class name
            return $entityName;
        } elseif (count($split) > 2) {
            throw new \InvalidArgumentException(
                sprintf(
                    'Incorrect entity name: %s. Expected the full class name or bundle:entity.',
                    $entityName
                )
            );
        }

        return $this->em->getConfiguration()->getEntityNamespace($split[0]) . '\\' . $split[1];
    }
}
