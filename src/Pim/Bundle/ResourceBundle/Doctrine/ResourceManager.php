<?php

namespace Pim\Bundle\ResourceBundle\Doctrine;

use Doctrine\Common\Persistence\ManagerRegistry;
use Doctrine\Common\Persistence\ObjectManager;
use Doctrine\Common\Util\ClassUtils;
use Pim\Component\Resource\Manager\ResourceManagerInterface;
use Pim\Component\Resource\ResourceInterface;
use Pim\Component\Resource\ResourceSetInterface;

/**
 * Base Doctrine resource manager
 *
 * @author    Julien Janvier <julien.janvier@akeneo.com>
 * @copyright 2014 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class ResourceManager implements ResourceManagerInterface
{
    /** @var ManagerRegistry */
    protected $registry;

    /**
     * @param ManagerRegistry $registry
     */
    public function __construct(ManagerRegistry $registry)
    {
        $this->registry = $registry;
    }

    /**
     * {@inheritdoc}
     */
    public function save(ResourceInterface $resource, $andFlush = true)
    {
        $manager = $this->getObjectManager($resource);
        $manager->persist($resource);

        if ($andFlush) {
            $manager->flush();
        }
    }

    /**
     * {@inheritDoc}
     */
    public function bulkSave(ResourceSetInterface $resources, $andFlush = true)
    {
        if (empty($resources->getResources())) {
            return;
        }

        $manager = $this->getObjectManager($resources[0]);
        foreach ($resources as $resource) {
            $manager->persist($resource);
        }

        if ($andFlush) {
            $manager->flush();
        }
    }

    /**
     * {@inheritDoc}
     */
    public function delete(ResourceInterface $resource, $andFlush = true)
    {
        $manager = $this->getObjectManager($resource);
        $manager->remove($resource);

        if ($andFlush) {
            $manager->flush();
        }
    }

    /**
     * {@inheritDoc}
     */
    public function bulkDelete(ResourceSetInterface $resources, $andFlush = true)
    {
        if (empty($resources->getResources())) {
            return;
        }

        $manager = $this->getObjectManager($resources[0]);
        foreach ($resources as $resource) {
            $manager->remove($resource);
        }

        if ($andFlush) {
            $manager->flush();
        }
    }

    /**
     * Get the resource's object manager.
     *
     * @param ResourceInterface $resource
     *
     * @return ObjectManager
     */
    private function getObjectManager(ResourceInterface $resource)
    {
        return $this->registry->getManagerForClass(ClassUtils::getClass($resource));
    }
}
