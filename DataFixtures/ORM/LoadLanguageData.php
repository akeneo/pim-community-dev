<?php
namespace Pim\Bundle\DemoBundle\DataFixtures\ORM;

use Doctrine\Common\Persistence\ObjectManager;

use Symfony\Component\DependencyInjection\ContainerInterface;

use Symfony\Component\DependencyInjection\ContainerAwareInterface;

use Doctrine\Common\DataFixtures\OrderedFixtureInterface;

use Doctrine\Common\DataFixtures\AbstractFixture;

/**
 * Load fixtures for languages
 *
 * @author    Romain Monceau <romain@akeneo.com>
 * @copyright 2012 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 *
 */
class LoadLanguageData extends AbstractFixture implements OrderedFixtureInterface, ContainerAwareInterface
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
     * Get language manager
     * @return \Oro\Bundle\FlexibleEntityBundle\Manager\SimpleManager
     */
    protected function getLanguageManager()
    {
        return $this->container->get('language_manager');
    }

    /**
     * {@inheritdoc}
     */
    public function load(ObjectManager $manager)
    {
        $this->createLanguage('fr_FR', 'fr_FR', array('EUR'));
        $this->createLanguage('en_US', 'en_EN', array('USD', 'EUR'));
        $this->createLanguage('en_EN', 'en_EN', array('GBP', 'EUR', 'USD'));
        $this->createLanguage('fr_CH', 'fr_FR', array('CHF', 'EUR'), false);

        $this->getLanguageManager()->getStorageManager()->flush();
    }

    /**
     * Create language entity and persist it
     * @param string  $code       Language code
     * @param string  $fallback   Language fallback
     * @param array   $currencies Currencies used
     * @param boolean $activated  Define if language is activated or not
     */
    protected function createLanguage($code, $fallback, $currencies = array(), $activated = true)
    {
        $language = $this->getLanguageManager()->createEntity();
        $language->setCode($code);
        $language->setFallback($fallback);
        $language->setActivated($activated);

        // prepare currencies
        $langCurrencies = array();
        foreach ($currencies as $code) {
            $langCurrencies[] = $this->getReference('currency.'. $code);
        }
        $language->setCurrencies($langCurrencies);

        $this->getLanguageManager()->getStorageManager()->persist($language);
        $this->setReference('language.'. $code, $language);
    }

    /**
     * {@inheritdoc}
     */
    public function getOrder()
    {
        return 1;
    }
}
