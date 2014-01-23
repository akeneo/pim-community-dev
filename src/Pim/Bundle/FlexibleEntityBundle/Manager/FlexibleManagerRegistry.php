<?php

namespace Pim\Bundle\FlexibleEntityBundle\Manager;

use Doctrine\Common\Util\ClassUtils;

/**
 * A registry which knows all flexible entity managers
 *
 * @author    Nicolas Dupont <nicolas@akeneo.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class FlexibleManagerRegistry
{
    /**
     * Managers references
     * @var \ArrayAccess
     */
    protected $managers;

    /**
     * Entity name to manager
     * @var \ArrayAccess
     */
    protected $entityToManager;

    /**
     * Constructor
     */
    public function __construct()
    {
        $this->managers        = array();
        $this->entityToManager = array();
    }

    /**
     * Add a manager to the registry
     *
     * @param string          $managerId  the manager id
     * @param FlexibleManager $manager    the manager
     * @param string          $entityFQCN the FQCN
     *
     * @return FlexibleManagerRegistry
     */
    public function addManager($managerId, FlexibleManager $manager, $entityFQCN)
    {
        $this->managers[$managerId]        = $manager;
        $this->entityToManager[$entityFQCN] = $manager;

        return $this;
    }

    /**
     * Get the list of manager id to manager services
     *
     * @return array
     */
    public function getManagers()
    {
        return $this->managers;
    }

    /**
     * Get the list of entity FQCN to related manager
     *
     * @return array
     */
    public function getEntityToManager()
    {
        return $this->entityToManager;
    }

    /**
     * Get the manager from the entity FQCN
     *
     * @param string $entityFQCN the entity FQCN
     *
     * @return FlexibleManager
     *
     * @throws \InvalidArgumentException If cannot get flexible manager
     */
    public function getManager($entityFQCN)
    {
        $realClassName = ClassUtils::getRealClass($entityFQCN);
        if (!isset($this->entityToManager[$realClassName])) {
            throw new \InvalidArgumentException(sprintf('Cannot get flexible manager for class "%s".', $entityFQCN));
        }

        return $this->entityToManager[$realClassName];
    }
}
