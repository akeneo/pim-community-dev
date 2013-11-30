<?php

namespace Pim\Bundle\DemoBundle\DataFixtures\ORM;

use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Doctrine\Common\Persistence\ObjectManager;
use Doctrine\Common\DataFixtures\OrderedFixtureInterface;
use Doctrine\Common\DataFixtures\AbstractFixture;

/**
 * Load data for category tree
 *
 * @author    Romain Monceau <romain@akeneo.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
abstract class AbstractDemoFixture extends AbstractFixture implements OrderedFixtureInterface, ContainerAwareInterface
{
    /**
     * @var \Symfony\Component\DependencyInjection\ContainerInterface
     */
    protected $container;

    /**
     * @var \Doctrine\Common\Persistence\ObjectManager
     */
    protected $manager;

    /**
     * {@inheritdoc}
     */
    public function setContainer(ContainerInterface $container = null)
    {
        $this->container = $container;
    }

    /**
     * check if demo data loadind is enabled
     *
     * @return boolean
     */
    public function isEnabled()
    {
        return $this->container->getParameter('pim_demo.load_data');
    }
}
