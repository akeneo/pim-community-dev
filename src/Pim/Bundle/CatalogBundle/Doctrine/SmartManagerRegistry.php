<?php

namespace Pim\Bundle\CatalogBundle\Doctrine;

use Doctrine\Common\Persistence\ManagerRegistry;

/**
 * Doctrine manager registry which is able to get object manager
 * for any registered registries.
 *
 * Common use case is to register the entity and document registry in it
 * and use the getManagerForClass() method to grab the correct manager.
 *
 * @author    Gildas Quemener <gildas@akeneo.com>
 * @copyright 2014 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class SmartManagerRegistry implements ManagerRegistry
{
    /** @var ManagerRegistry[] */
    protected $registries = [];

    /**
     * {@inheritdoc}
     */
    public function getDefaultManagerName()
    {
        throw new \LogicException('Not smart enough');
    }

    /**
     * {@inheritdoc}
     */
    public function getManager($name = null)
    {
        throw new \LogicException('Not smart enough');
    }

    /**
     * {@inheritdoc}
     */
    public function getManagers()
    {
        $managers = [];
        foreach ($this->registries as $registry) {
            $managers = array_merge(array_values($registry->getManagers()), $managers);
        }

        return $managers;
    }

    /**
     * {@inheritdoc}
     */
    public function resetManager($name = null)
    {
        throw new \LogicException('Not smart enough');
    }

    /**
     * {@inheritdoc}
     */
    public function getAliasNamespace($alias)
    {
        foreach ($this->registries as $registry) {
            try {
                return $registry->getAliasNamespace($alias);
            } catch (\Exception $e) { //TODO: catch more precise exception
                continue;
            }
        }

        throw new \LogicException(sprintf('Can not resolve alias namespace "%s"', $alias));
    }

    /**
     * Gets all connection names.
     *
     * @return array An array of connection names.
     */
    public function getManagerNames()
    {
        throw new \LogicException('Not smart enough');
    }

    /**
     * {@inheritdoc}
     */
    public function getRepository($persistentObject, $persistentManagerName = null)
    {
        return $this->getManagerForClass($persistentObject)->getRepository($persistentObject);
    }

    /**
     * {@inheritdoc}
     */
    public function getManagerForClass($class)
    {
        foreach ($this->registries as $registry) {
            if ($manager = $registry->getManagerForClass($class)) {
                return $manager;
            }
        }

        throw new \Exception('No manager was found for '. $class);
    }

    /**
     * @param ManagerRegistry $registry
     */
    public function addRegistry(ManagerRegistry $registry)
    {
        $this->registries[] = $registry;
    }

    /**
     * {@inheritdoc}
     */
    public function getDefaultConnectionName()
    {
        throw new \LogicException('Not smart enough');
    }

    /**
     * {@inheritdoc}
     */
    public function getConnection($name = null)
    {
        throw new \LogicException('Not smart enough');
    }

    /**
     * {@inheritdoc}
     */
    public function getConnections()
    {
        $connections = [];
        foreach ($this->getManagers() as $manager) {
            $connections[] = $manager->getConnection();
        }

        return $connections;
    }

    /**
     * {@inheritdoc}
     */
    public function getConnectionNames()
    {
        throw new \LogicException('Not smart enough');
    }
}
