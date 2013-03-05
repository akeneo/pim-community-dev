<?php
namespace Pim\Bundle\DemoBundle\DataFixtures\ORM;

use Doctrine\Common\Persistence\ObjectManager;

use Symfony\Component\DependencyInjection\ContainerInterface;

use Symfony\Component\DependencyInjection\ContainerAwareInterface;

use Doctrine\Common\DataFixtures\OrderedFixtureInterface;

use Doctrine\Common\DataFixtures\AbstractFixture;

/**
 * Load fixtures for currencies
 *
 * @author    Romain Monceau <romain@akeneo.com>
 * @copyright 2012 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 *
 */
class LoadCurrencyData extends AbstractFixture implements OrderedFixtureInterface, ContainerAwareInterface
{

    /**
     * @var \Symfony\Component\DependencyInjection\ContainerInterface
     */
    protected $container;

    /**
     * Object manager
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
     * Get currency manager
     * @return \Oro\Bundle\FlexibleEntityBundle\Manager\SimpleManager
     */
    protected function getCurrencyManager()
    {
        return $this->container->get('currency_manager');
    }

    /**
     * {@inheritdoc}
     */
    public function load(ObjectManager $manager)
    {
        // create currencies
        $this->createCurrency('EUR');
        $this->createCurrency('USD');
        $this->createCurrency('GBP');
        $this->createCurrency('CHF', false);

        $this->getCurrencyManager()->getStorageManager()->flush();
    }

    /**
     * Create currency entity and persist it
     * @param string  $code      Currency code
     * @param boolean $activated Define if currency is activated or not
     */
    protected function createCurrency($code, $activated = true)
    {
        $currency = $this->getCurrencyManager()->createEntity();
        $currency->setCode($code);
        $currency->setActivated($activated);
        $this->getCurrencyManager()->getStorageManager()->persist($currency);
        $this->setReference('currency.'. $code, $currency);
    }

    /**
     * {@inheritdoc}
     */
    public function getOrder()
    {
        return 0;
    }
}
