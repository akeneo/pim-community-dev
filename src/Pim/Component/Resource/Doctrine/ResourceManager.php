<?php

namespace Pim\Component\Resource\Domain\Manager;

use Doctrine\Common\Persistence\ObjectManager;
use Pim\Bundle\CatalogBundle\Doctrine\SmartManagerRegistry;
use Pim\Component\Resource\Domain\ResourceInterface;
use Pim\Component\Resource\Domain\ResourceSetInterface;

/**
 * Base Doctrine resource manager
 *
 * @author    Julien Janvier <julien.janvier@akeneo.com>
 * @copyright 2014 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class ResourceManager implements ResourceManagerInterface
{
    /** @var SmartManagerRegistry */
    protected $smartManagerRegistry;

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
        if (!empty($resources)) {
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
        if (!empty($resources)) {
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
        return $this->smartManagerRegistry->getManagerForClass(get_class($resource));
    }
}
