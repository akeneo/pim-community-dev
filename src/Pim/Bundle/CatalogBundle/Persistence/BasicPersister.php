<?php

namespace Pim\Bundle\CatalogBundle\Persistence;

use Doctrine\Common\Persistence\ManagerRegistry;
use Pim\Bundle\CatalogBundle\Manager\ResourceManagerInterface;
use Pim\Bundle\VersioningBundle\Model\VersionableInterface;
use Pim\Bundle\VersioningBundle\Manager\VersionManager;

/**
 * Synchronize object with the database
 *
 * @author    Gildas Quemener <gildas@akeneo.com>
 * @copyright 2014 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class BasicPersister implements ResourceManagerInterface
{
    /** @var ManagerRegistry */
    protected $registry;

    /** @var VersionManager */
    protected $versionManager;

    /**
     * @param ManagerRegistry     $registry
     * @param VersionManager      $versionManager
     */
    public function __construct(ManagerRegistry $registry, VersionManager $versionManager)
    {
        $this->registry       = $registry;
        $this->versionManager = $versionManager;
    }

    /**
     * {@inheritdoc}
     */
    public function save($object, array $options = [])
    {
        $options = array_merge(['flush' => true], $options);

        $manager = $this->registry->getManagerForClass(get_class($object));
        $manager->persist($object);

        if ($options['flush']) {
            $manager->flush();
        }

        if ($options['versioning'] && $object instanceof VersionableInterface) {
            $changeset = [];
            $versions = $this->versionManager->buildVersions($object, $changeset);
            foreach ($versions as $version) {
                if ($version->getChangeset()) {
                    $manager->persist($version);
                }
            }
            if ($options['flush']) {
                $manager->flush();
            }
        }
    }

    /**
     * {@inheritdoc}
     */
    public function saveAll(array $objects, array $options = [])
    {
        $options = array_merge(['flush' => true], $options);

        // TODO option resolver + deal with versioning

        if (0 === count($objects)) {
            return;
        }
        // TODO : to fix
        $firstObject = $objects[0];
        $manager = $this->registry->getManagerForClass(get_class($firstObject));

        $itemOptions = $options;
        $itemOptions['flush'] = false;
        foreach ($objects as $object) {
            $this->save($object, $itemOptions);
        }

        if ($options['flush']) {
            $manager->flush();
        }

        if ($options['versioning'] && $firstObject instanceof VersionableInterface) {
            foreach ($objects as $object) {
                $changeset = [];
                $versions = $this->versionManager->buildVersions($object, $changeset);
                foreach ($versions as $version) {
                    if ($version->getChangeset()) {
                        $manager->persist($version);
                    }
                }
            }
            if ($options['flush']) {
                $manager->flush();
            }
        }
    }
}
