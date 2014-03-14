<?php

namespace Pim\Bundle\CatalogBundle\Doctrine;

use Doctrine\Common\Persistence\ManagerRegistry;

/**
 * 
 * @author    Gildas Quemener <gildas@akeneo.com>
 * @copyright 2014 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class SmartManagerRegistry implements ManagerRegistry
{
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
        return $this->managers;
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
        throw new \LogicException('Not smart enough');
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
            if ($result = $registry->getManagerForClass($class)) {
                return $result;
            }
        }

        throw new \Exception('No manager were found for '. $class);
    }

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
        throw new \LogicException('Not smart enough');
    }

    /**
     * {@inheritdoc}
     */
    public function getConnectionNames()
    {
        throw new \LogicException('Not smart enough');
    }
}
