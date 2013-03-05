<?php
namespace Pim\Bundle\DemoBundle\DataFixtures\ORM;

use Pim\Bundle\ConfigBundle\Entity\Currency;

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
     * {@inheritdoc}
     */
    public function setContainer(ContainerInterface $container = null)
    {
        $this->container = $container;
    }

    /**
     * {@inheritdoc}
     */
    public function load(ObjectManager $manager)
    {
        // create currencies
        $currency = $this->createCurrency('EUR');
        $manager->persist($currency);

        $currency = $this->createCurrency('USD');
        $manager->persist($currency);

        $currency = $this->createCurrency('GBP');
        $manager->persist($currency);

        $currency = $this->createCurrency('CHF', false);
        $manager->persist($currency);

        $manager->flush();
    }

    /**
     * Create currency entity and persist it
     * @param string  $code      Currency code
     * @param boolean $activated Define if currency is activated or not
     *
     * @return \Pim\Bundle\ConfigBundle\Entity\Currency
     */
    protected function createCurrency($code, $activated = true)
    {
        $currency = new Currency();
        $currency->setCode($code);
        $currency->setActivated($activated);

        $this->setReference('currency.'. $code, $currency);

        return $currency;
    }

    /**
     * {@inheritdoc}
     */
    public function getOrder()
    {
        return 0;
    }
}
