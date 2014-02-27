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
        foreach ($this->registry->getFixtures($paths) as $fixtureConfig) {
            $loader = $this->factory->create(
                $objectManager,
                $referenceRepository,
                $fixtureConfig['name'],
                $fixtureConfig['extension']
            );
            $loader->load($fixtureConfig['path']);
        }
    }
}
