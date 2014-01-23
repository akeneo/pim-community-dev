<?php

namespace Pim\Bundle\InstallerBundle\FixtureLoader;

use Doctrine\Common\DataFixtures\ReferenceRepository;
use Doctrine\Common\Persistence\ObjectManager;

/**
 * Loads multiple fixture files
 *
 * @author    Antoine Guigan <antoine@akeneo.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class MultipleLoader
{
    /**
     * @var ConfigurationRegistryInterface
     */
    protected $registry;

    /**
     * @var LoaderFactory
     */
    protected $factory;

    /**
     * Constructor
     *
     * @param ConfigurationRegistryInterface $registry
     * @param LoaderFactory                  $factory
     */
    public function __construct(ConfigurationRegistryInterface $registry, LoaderFactory $factory)
    {
        $this->registry = $registry;
        $this->factory  = $factory;
    }

    /**
     * Loads multiple fixture files
     *
     * @param ObjectManager       $objectManager
     * @param ReferenceRepository $referenceRepository
     * @param array               $paths
     */
    public function load(ObjectManager $objectManager, ReferenceRepository $referenceRepository, array $paths)
    {
        $fileIndex = [];
        foreach ($paths as $path) {
            $parts = explode('.', basename($path));
            $file = [
                'path'      => $path,
                'extension' => array_pop($parts),
                'name'      => implode('.', $parts)
            ];
            if ($this->registry->contains($file['name'])) {
                $order = $this->registry->getOrder($file['name']);
                if (!isset($fileIndex[$order])) {
                    $fileIndex[$order] = [];
                }
                $fileIndex[$order][] = $file;
            }
        }

        ksort($fileIndex);
        foreach ($fileIndex as $files) {
            $this->loadFiles($objectManager, $referenceRepository, $files);
        }
    }

    /**
     * Loads sorted fixture files
     *
     * @param ObjectManager       $objectManager
     * @param ReferenceRepository $referenceRepository
     * @param array               $files
     */
    protected function loadFiles(ObjectManager $objectManager, ReferenceRepository $referenceRepository, array $files)
    {
        foreach ($files as $file) {
            $loader = $this->factory->create($objectManager, $referenceRepository, $file['name'], $file['extension']);
            $loader->load($file['path']);
        }
    }
}
