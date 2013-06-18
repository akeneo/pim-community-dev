<?php
namespace Pim\Bundle\DemoBundle\DataFixtures\ORM;

use Pim\Bundle\ConfigBundle\Entity\Currency;
use Pim\Bundle\ConfigBundle\Entity\Locale;
use Doctrine\Common\Persistence\ObjectManager;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Doctrine\Common\DataFixtures\OrderedFixtureInterface;
use Doctrine\Common\DataFixtures\AbstractFixture;

/**
 * Load fixtures for locales
 *
 * @author    Romain Monceau <romain@akeneo.com>
 * @copyright 2012 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 *
 */
class LoadLocaleData extends AbstractFixture implements OrderedFixtureInterface, ContainerAwareInterface
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
        $locale = $this->createLocale('fr_FR', null, 'EUR');
        $manager->persist($locale);

        $locale = $this->createLocale('de_DE', null, 'EUR');
        $manager->persist($locale);

        $locale = $this->createLocale('en_GB', null, 'GBP');
        $manager->persist($locale);

        $locale = $this->createLocale('fr_CA', 'fr_FR', 'CAD', false);
        $manager->persist($locale);

        $manager->flush();
    }

    /**
     * Create locale entity and persist it
     * @param string  $code         Locale code
     * @param string  $fallback     Locale fallback
     * @param string  $currencyCode Currencies used
     * @param boolean $activated    Define if locale is activated or not
     *
     * @return \Pim\Bundle\ConfigBundle\Entity\Locale
     */
    protected function createLocale($code, $fallback, $currencyCode, $activated = true)
    {
        $locale = new Locale();
        $locale->setCode($code);
        $locale->setFallback($fallback);
        $locale->setActivated($activated);

        // prepare currencies
        $localeCurrency = $this->getReference('currency.'. $currencyCode);
        $locale->setDefaultCurrency($localeCurrency);

        $this->setReference('locale.'. $code, $locale);

        return $locale;
    }

    /**
     * {@inheritdoc}
     */
    public function getOrder()
    {
        return 5;
    }
}
