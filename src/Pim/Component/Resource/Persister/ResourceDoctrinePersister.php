<?php

namespace Pim\Component\Resource\Persister;

use Doctrine\Common\Persistence\ManagerRegistry;
use Doctrine\Common\Persistence\ObjectManager;
use Doctrine\Common\Util\ClassUtils;
use Pim\Component\Resource\Persister\ResourcePersisterInterface;
use Pim\Component\Resource\Model\ResourceInterface;
use Pim\Component\Resource\Model\ResourceSet;
use Pim\Component\Resource\Model\ResourceSetInterface;

/**
 * Base Doctrine resource persister
 *
 * @author    Julien Janvier <julien.janvier@akeneo.com>
 * @copyright 2014 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class ResourceDoctrinePersister implements ResourcePersisterInterface
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
     * {@inheritdoc}
     */
    public function createResourceSet(array $resources)
    {
        return new ResourceSet($resources);
    }

    /**
     * {@inheritdoc}
     */
    public function getObjectManagerTransitional($class)
    {
        return $this->registry->getManagerForClass($class);
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
