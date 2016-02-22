<?php

namespace Pim\Bundle\InstallerBundle\DataFixtures\ORM;

use Akeneo\Bundle\BatchBundle\Item\InvalidItemException;
use Doctrine\Common\DataFixtures\AbstractFixture;
use Doctrine\Common\DataFixtures\OrderedFixtureInterface as OrderedFixtureInt;
use Symfony\Component\DependencyInjection\ContainerAwareInterface as ContainerAwareInt;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\Finder\Finder;

/**
 * Abstract installer fixture
 *
 * @author    Nicolas Dupont <nicolas@akeneo.com>
 * @author    JM Leroux <jean-marie.leroux@akeneo.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
abstract class AbstractInstallerFixture extends AbstractFixture implements OrderedFixtureInt, ContainerAwareInt
{
    /** @var ContainerInterface */
    protected $container;

    /** @var string[] */
    protected $files;

    /**
     * {@inheritdoc}
     */
    public function setContainer(ContainerInterface $container = null)
    {
        $this->container = $container;
        $this->files     = $this->addInstallerDataFiles($container);
    }

    /**
     * Retrieve fixtures files
     *
     * @param ContainerInterface $container
     *
     * @return string[]
     */
    protected function addInstallerDataFiles(ContainerInterface $container)
    {
        $installerDataDir = null;
        $installerData    = $container->getParameter('installer_data');

        if (preg_match('/^(?P<bundle>\w+):(?P<directory>\w+)$/', $installerData, $matches)) {
            $bundles          = $container->getParameter('kernel.bundles');
            $reflection       = new \ReflectionClass($bundles[$matches['bundle']]);
            $installerDataDir = dirname($reflection->getFilename()) . '/Resources/fixtures/' . $matches['directory'];
        } else {
            $installerDataDir = $container->getParameter('installer_data');
        }

        if ('/' !== substr($installerDataDir, -1, 1)) {
            $installerDataDir .= '/';
        }

        $installerFiles = [];

        $finder = new Finder();
        $finder->in($installerDataDir)->name('/\.(yml|csv)$/');

        foreach ($finder as $file) {
            $info = pathinfo($file->getFileName());
            $entity = basename($file->getFileName(), '.'.$info['extension']);
            $installerFiles[$entity] = $file->getPathName();
        }

        return $installerFiles;
    }

    /**
     * @return string
     */
    public function getFilePath()
    {
        return $this->files[$this->getEntity()];
    }

    /**
     * Validate the entity
     *
     * @param object $entity
     * @param array  $item
     *
     * @throws InvalidItemException
     */
    public function validate($entity, $item)
    {
        $validator  = $this->container->get('validator');
        $violations = $validator->validate($entity);
        if ($violations->count() > 0) {
            $messages = array();
            foreach ($violations as $violation) {
                $messages[] = (string) $violation;
            }

            throw new InvalidItemException(implode(', ', $messages), [$item]);
        }
    }

    /**
     * Must contains the end of the filename
     *
     * @return string
     */
    abstract public function getEntity();
}
