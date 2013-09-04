<?php

namespace Pim\Bundle\InstallerBundle\DataFixtures\ORM;

use Doctrine\Common\Persistence\ObjectManager;
use Pim\Bundle\CatalogBundle\Entity\Locale;

/**
 * Load fixtures for locales
 *
 * @author    Romain Monceau <romain@akeneo.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class LoadLocaleData extends AbstractInstallerFixture
{
    /**
     * {@inheritdoc}
     */
    public function load(ObjectManager $manager)
    {
        $allLocales = $this->container->getParameter('pim_catalog.locales');

        foreach (array_keys($allLocales['locales']) as $localeCode) {
            $locale = new Locale();
            $locale->setCode($localeCode);
            $this->setReference('locale.'. $localeCode, $locale);
            $manager->persist($locale);
        }

        $manager->flush();
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
