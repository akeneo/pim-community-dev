<?php
namespace Pim\Bundle\InstallerBundle\DataFixtures\ORM;

use Symfony\Component\Yaml\Yaml;
use Doctrine\Common\Persistence\ObjectManager;
use Pim\Bundle\ConfigBundle\Entity\Locale;

/**
 * Load fixtures for locales
 *
 * @author    Romain Monceau <romain@akeneo.com>
 * @copyright 2012 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 *
 */
class LoadLocaleData extends AbstractInstallerFixture
{
    /**
     * {@inheritdoc}
     */
    public function load(ObjectManager $manager)
    {
        $allLocales = $this->container->getParameter('pim_config.locales');
        $activatedLocales = Yaml::parse(realpath($this->getFilePath()));

        foreach (array_keys($allLocales['locales']) as $localeCode) {
            $activated = in_array($localeCode, array_keys($activatedLocales['locales']));
            $locale = $this->createLocale($localeCode, $activated);

            if ($activated) {
                $fallback     = $activatedLocales['locales'][$localeCode]['fallback'];
                $currencyCode = $activatedLocales['locales'][$localeCode]['currency'];
                $currency     = $this->getReference('currency.'. $currencyCode);

                $locale->setFallback($fallback);
                $locale->setDefaultCurrency($currency);
            }

            $this->setReference('locale.'. $localeCode, $locale);
            $manager->persist($locale);
        }

        $manager->flush();
    }

    /**
     * Create locale entity and persist it
     *
     * @param string  $code      Locale code
     * @param boolean $activated Define if locale is activated or not
     *
     * @return \Pim\Bundle\ConfigBundle\Entity\Locale
     */
    protected function createLocale($code, $activated = false)
    {
        $locale = new Locale();
        $locale->setCode($code);
        $locale->setActivated($activated);

        return $locale;
    }

    /**
     * {@inheritdoc}
     */
    public function getEntity()
    {
        return 'locales';
    }

    /**
     * {@inheritdoc}
     */
    public function getOrder()
    {
        return 2;
    }
}
