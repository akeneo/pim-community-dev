<?php

namespace Oro\Bundle\EntityBundle\ORM;

use Symfony\Bridge\Doctrine\ManagerRegistry;

/**
 * This class allows to get the real class name of an entity by its name
 */
class EntityClassResolver
{
    /**
     * @var ManagerRegistry
     */
    protected $doctrine;

    /**
     * Constructor
     *
     * @param ManagerRegistry $doctrine
     */
    public function __construct(ManagerRegistry $doctrine)
    {
        $this->doctrine = $doctrine;
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

        return $this->doctrine->getAliasNamespace($split[0]) . '\\' . $split[1];
    }

    /**
     * Checks whether the given namespace is registered in the Doctrine
     *
     * @param string $namespace
     * @return bool
     */
    public function isKnownEntityClassNamespace($namespace)
    {
        foreach (array_keys($this->doctrine->getManagers()) as $name) {
            $namespaces = $this->doctrine->getManager($name)->getConfiguration()->getEntityNamespaces();
            if (in_array($namespace, $namespaces, true)) {
                return true;
            }
        }

        return false;
    }

    /**
     * Check if given class is real entity class
     *
     * @param string $className
     * @return bool
     */
    public function isEntity($className)
    {
        return (!is_null($this->doctrine->getManagerForClass($className)));
    }
}
