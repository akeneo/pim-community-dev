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
        $bundle = new \ReflectionClass('Pim\Bundle\InstallerBundle\PimInstallerBundle');
        $locales = array_map('trim', file(sprintf('%s/Resources/config/locales', dirname($bundle->getFileName()))));
        $newLocales = [];
        foreach ($locales as $localeCode) {
            $locale = new Locale();
            $locale->setCode($localeCode);
            $this->setReference(get_class($locale).'.'.$localeCode, $locale);
            $this->validate($locale, $localeCode);
            $newLocales[] = $locale;
        }
        $this->container->get('pim_catalog.saver.locale')->saveAll($newLocales);
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
