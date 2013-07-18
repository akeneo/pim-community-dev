<?php
namespace Pim\Bundle\InstallerBundle\DataFixtures\ORM;

use Pim\Bundle\ConfigBundle\Entity\Currency;
use Pim\Bundle\ConfigBundle\Entity\Locale;
use Doctrine\Common\Persistence\ObjectManager;
use Symfony\Component\Yaml\Yaml;

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
        $activatedLocales = Yaml::parse(realpath($this->getFilePath()));

        foreach ($activatedLocales['locales'] as $code => $data) {
            $locale = $this->createLocale($code, $data['fallback'], $data['currency']);
            $manager->persist($locale);
        }

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
        $locale->setDefaultCurrency($this->getReference('currency.'. $currencyCode));
        $this->setReference('locale.'. $code, $locale);

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
