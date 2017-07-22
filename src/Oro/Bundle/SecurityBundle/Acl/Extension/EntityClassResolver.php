<?php

namespace Oro\Bundle\SecurityBundle\Acl\Extension;

use Symfony\Bridge\Doctrine\RegistryInterface;

/**
 * This class allows to get the real class name of an entity by its name
 *
 * @author    Julien Janvier <julien.janvier@akeneo.com>
 * @copyright 2014 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class EntityClassResolver
{
    /**
     * @var RegistryInterface
     */
    protected $doctrine;

    /**
     * Constructor
     *
     * @param RegistryInterface $doctrine
     */
    public function __construct(RegistryInterface $doctrine)
    {
        $this->doctrine = $doctrine;
    }

    /**
     * Gets the full class name for the given entity
     *
     * @param string $entityName The name of the entity. Can be Bundle:Entity or full class name
     *
     * @throws \InvalidArgumentException
     *
     * @return string The full class name
     */
    public function getEntityClass($entityName)
    {
        $parts = explode(':', $entityName);
        if (count($parts) <= 1) {
            // The given entity name is not in bundle:entity format. Suppose that it is the full class name
            return $entityName;
        } elseif (count($parts) > 2) {
            throw new \InvalidArgumentException(
                sprintf(
                    'Incorrect entity name: %s. Expected the full class name or bundle:entity.',
                    $entityName
                )
            );
        }

        return $this->doctrine->getAliasNamespace($parts[0]) . '\\' . $parts[1];
    }

    /**
     * Checks if the given class is real entity class
     *
     * @param string $className
     *
     * @return bool
     */
    public function isEntity($className)
    {
        return (!is_null($this->doctrine->getManagerForClass($className)));
    }
}
