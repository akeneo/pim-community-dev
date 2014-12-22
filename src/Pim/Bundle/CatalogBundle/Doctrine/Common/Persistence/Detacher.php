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
 * TODO : should be move in storage utils once https://github.com/akeneo/pim-community-dev/pull/1874 merged
 */
class Detacher implements DetacherInterface
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
     * {@inheritdoc}
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
    protected function getObjectManager($object)
    {
        return $this->managerRegistry->getManagerForClass(ClassUtils::getClass($object));
    }
}