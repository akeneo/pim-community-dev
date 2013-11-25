<?php

namespace Pim\Bundle\InstallerBundle\DataFixtures\ORM;

use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\DependencyInjection\ContainerAwareInterface as ContainerAwareInt;
use Doctrine\Common\DataFixtures\OrderedFixtureInterface as OrderedFixtureInt;
use Doctrine\Common\DataFixtures\AbstractFixture;
use Oro\Bundle\BatchBundle\Item\InvalidItemException;

/**
 * Abstract installer fixture
 *
 * @author    Nicolas Dupont <nicolas@akeneo.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 *
 * @abstract
 */
abstract class AbstractInstallerFixture extends AbstractFixture implements OrderedFixtureInt, ContainerAwareInt
{
    /**
     * @var \Symfony\Component\DependencyInjection\ContainerInterface
     */
    protected $container;

    /**
     * array
     */
    protected $files;

    /**
     * {@inheritdoc}
     */
    public function setContainer(ContainerInterface $container = null)
    {
        $this->container = $container;
        $this->files = $container->getParameter('pim_installer.files');
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
        $validator = $this->container->get('validator');
        $violations = $validator->validate($entity);
        if ($violations->count() > 0) {
            $messages = array();
            foreach ($violations as $violation) {
                $messages[] = (string) $violation;
            }

            throw new InvalidItemException(implode(', ', $messages), $item);
        }
    }

    /**
     * Must contains the end of the filename
     *
     * @abstract
     * @return string
     */
    abstract public function getEntity();
}
