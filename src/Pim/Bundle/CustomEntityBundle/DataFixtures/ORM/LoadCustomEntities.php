<?php

namespace Pim\Bundle\CustomEntityBundle\DataFixtures\ORM;

use Doctrine\Common\DataFixtures\AbstractFixture;
use Doctrine\Common\Persistence\ObjectManager;
use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpKernel\KernelInterface;
use Pim\Bundle\CustomEntityBundle\Configuration\Registry;
use Pim\Bundle\ImportExportBundle\Transformer\OrmTransformer;
use Pim\Bundle\InstallerBundle\Transformer\Property\FixtureReferenceTransformer;

/**
 * Fixture for custom entities
 *
 * @author    Antoine Guigan <antoine@akeneo.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class LoadCustomEntities extends AbstractFixture implements ContainerAwareInterface
{
    /**
     * @var ContainerInterface
     */
    protected $container;

    /**
     * {@inheritdoc}
     */
    public function load(ObjectManager $manager)
    {
        $transformer = $this->getFixtureReferenceTransformer();
        $transformer->setReferenceRepository($this->referenceRepository);
        foreach ($this->getFiles() as $class => $filePath) {
            $this->loadFile($manager, $filePath, $class);
        }
        $transformer->setReferenceRepository(null);
    }

    /**
     * Load fixture file
     *
     * @param ObjectManager $manager
     * @param string        $filePath
     * @param string        $class
     */
    public function loadFile(ObjectManager $manager, $filePath, $class)
    {
        $f = fopen($filePath, 'r');
        $labels = array_map('trim', fgetcsv($f, 0, ';'));
        $transformer = $this->getOrmTransformer();
        while ($row = fgetcsv($f, 0, ';')) {
            $object = $transformer->transform(
                $class,
                array_combine($labels, $row)
            );
            $manager->persist($object);
            $this->addReference(sprintf('%s.%s', $class, $object->getCode()), $object);
        }
        $manager->flush();
    }

    /**
     * {@inheritdoc}
     */
    public function setContainer(ContainerInterface $container = null)
    {
        $this->container = $container;
    }

    /**
     * Returns an array of fixture files
     *
     * @return array
     */
    protected function getFiles()
    {
        $files = array();
        $registry = $this->getCustomEntityRegistry();
        foreach ($this->getKernel()->getBundles() as $bundle) {
            $bundleFiles = glob(sprintf('%s/Resources/fixtures/*.csv', $bundle->getPath()));
            foreach ($bundleFiles as $filePath) {
                $parts = explode('-', basename($filePath, '.csv'));
                $customEntityName = array_pop($parts);
                if ($registry->has($customEntityName)) {
                    $class = $registry->get($customEntityName)->getEntityClass();
                    $files[$class] = $filePath;
                }
            }
        }

        return $files;
    }

    /**
     * Returns the custom entity registry
     *
     * @return Registry
     */
    protected function getCustomEntityRegistry()
    {
        return $this->container->get('pim_custom_entity.configuration.registry');
    }

    /**
     * Returns the ORM transformer
     *
     * @return OrmTransformer
     */
    protected function getOrmTransformer()
    {
        return $this->container->get('pim_import_export.transformer.orm');
    }

    /**
     * Returns the fixture reference transformer
     *
     * @return FixtureReferenceTransformer
     */
    protected function getFixtureReferenceTransformer()
    {
        return $this->container->get('pim_installer.transformer.property.fixture_reference');
    }

    /**
     * Returns the Kernel
     *
     * @return KernelInterface
     */
    protected function getKernel()
    {
        return $this->container->get('kernel');
    }
}
