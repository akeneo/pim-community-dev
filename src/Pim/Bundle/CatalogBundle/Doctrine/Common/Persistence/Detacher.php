<?php

namespace Pim\Bundle\CatalogBundle\Doctrine\Common\Persistence;

use Doctrine\Common\Persistence\ManagerRegistry;
use Doctrine\Common\Persistence\ObjectManager;
use Doctrine\Common\Util\ClassUtils;

/**
 * Detacher, detachs an object from its ObjectManager
 *
 * @author    Nicolas Dupont <nicolas@akeneo.com>
 * @copyright 2014 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 *
 * TODO : add an interface ?
 */
class Detacher
{
    /** @var ManagerRegistry */
    protected $managerRegistry;

    /**
     * @param ManagerRegistry $registry
     */
    public function __construct(ManagerRegistry $registry)
    {
        $this->managerRegistry = $registry;
    }

    /**
     * @param object $object
     */
    public function detach($object)
    {
        $objectManager = $this->getObjectManager($object);
        $objectManager->detach($object);
    }

    /**
     * @param object $object
     *
     * @return ObjectManager
     */
    public function getObjectManager($object)
    {
        return $this->managerRegistry->getManagerForClass(ClassUtils::getClass($object));
    }
}