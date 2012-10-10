<?php
namespace Akeneo\CatalogBundle\DataFixtures\ORM;

use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Doctrine\Common\DataFixtures\AbstractFixture;
use Doctrine\Common\DataFixtures\OrderedFixtureInterface;
use Doctrine\Common\Persistence\ObjectManager;
use Akeneo\CatalogBundle\Model\ProductType;
use Akeneo\CatalogBundle\Model\BaseFieldFactory;

/**
 * Load base configuration for module
 *
 * Execute with "php app/console doctrine:fixtures:load"
 *
 * @author    Nicolas Dupont <nicolas@akeneo.com>
 * @copyright Copyright (c) 2012 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 *
 */
class LoadConfig extends AbstractFixture implements OrderedFixtureInterface, ContainerAwareInterface
{

    /**
    * @var ContainerInterface
    */
    private $container;

    /**
     * {@inheritDoc}
     */
    public function setContainer(ContainerInterface $container = null)
    {
        $this->container = $container;
    }

    /**
     * {@inheritDoc}
     */
    public function load(ObjectManager $manager)
    {
        // TODO load config in config entity
    }

    /**
     * Executing order
     * @see Doctrine\Common\DataFixtures.OrderedFixtureInterface::getOrder()
     */
    public function getOrder()
    {
        return 1;
    }

}