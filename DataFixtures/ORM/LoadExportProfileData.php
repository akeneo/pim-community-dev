<?php

namespace Pim\Bundle\DemoBundle\DataFixtures\ORM;

use Pim\Bundle\ProductBundle\Entity\ExportProfileTranslation;
use Pim\Bundle\ProductBundle\Entity\ExportProfile;
use Doctrine\Common\Persistence\ObjectManager;
use Oro\Bundle\FlexibleEntityBundle\Manager\FlexibleManager;
use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Doctrine\Common\DataFixtures\OrderedFixtureInterface;
use Doctrine\Common\DataFixtures\AbstractFixture;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Load fixtures for export profiles
 *
 * @author    Romain Monceau <romain@akeneo.com>
 * @copyright 2012 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 *
 */
class LoadExportProfileData extends AbstractDemoFixture
{
    /**
     * {@inheritdoc}
     */
    public function load(ObjectManager $manager)
    {
        if ($this->isEnabled() === false) {
            return;
        }

        $this->manager = $manager;

        // create profiles
        $profile = $this->createProfile('Skeleton');
        $this->persist($profile, 'export-profile.skeleton');

        $profile = $this->createProfile('IPad');
        $this->persist($profile, 'export-profile.ipad');

        $profile = $this->createProfile('E-Commerce');
        $this->persist($profile, 'export-profile.ecommerce');


        // translate profiles in en_US
        $locale = 'en_US';
        $this->translate('export-profile.skeleton', $locale, 'Skeleton');
        $this->translate('export-profile.ipad', $locale, 'IPad');
        $this->translate('export-profile.ecommerce', $locale, 'E-Commerce');

        // translate profiles in en_GB
        $locale = 'en_GB';
        $this->translate('export-profile.skeleton', $locale, 'Skeleton');
        $this->translate('export-profile.ipad', $locale, 'IPad');
        $this->translate('export-profile.ecommerce', $locale, 'E-Commerce');

        // translate profiles in fr_FR
        $locale = 'fr_FR';
        $this->translate('export-profile.skeleton', $locale, 'Squelette');
        $this->translate('export-profile.ipad', $locale, 'IPad');
        $this->translate('export-profile.ecommerce', $locale, 'E-Commerce');

        $this->manager->flush();
    }

    /**
     * Create a profile
     *
     * @param string $name
     *
     * @return \Pim\Bundle\ProductBundle\Entity\ExportProfile
     */
    protected function createProfile($name)
    {
        $profile = new ExportProfile();
        $profile->setCode(strtolower($name));
        $profile->setName($name .' (default)');

        $translation = $this->createTranslation($profile, 'default', $name .' (default)');
        $profile->addTranslation($translation);

        return $profile;
    }

    /**
     * Persist entity and add reference
     *
     * @param ExportProfile $profile   export profile entity
     * @param string        $reference object reference to reuse it
     */
    protected function persist(ExportProfile $profile, $reference)
    {
        $this->manager->persist($profile);

        $this->addReference($reference, $profile);
    }

    /**
     * Translate an export profile
     *
     * @param string $reference Export profile reference
     * @param string $locale    Locale used
     * @param string $name      Name translated in locale value linked
     */
    protected function translate($reference, $locale, $name)
    {
        $profile = $this->getReference($reference);

        $translation = $this->createTranslation($profile, $locale, $name);
        $profile->addTranslation($translation);

        $this->manager->persist($profile);
    }

    /**
     * Create a translation entity
     *
     * @param ExportProfile $entity ExportProfile entity
     * @param string        $locale Locale used
     * @param string        $name   Name translated in locale value linked
     *
     * @return \Pim\Bundle\ProductBundle\Entity\ExportProfileTranslation
     */
    protected function createTranslation($entity, $locale, $name)
    {
        $translation = new ExportProfileTranslation();
        $translation->setForeignKey($entity);
        $translation->setLocale($locale);
        $translation->setName($name);

        return $translation;
    }

    /**
     * {@inheritdoc}
     */
    public function getOrder()
    {
        return 10;
    }
}
