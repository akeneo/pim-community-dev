<?php

namespace Pim\Bundle\InstallerBundle\DataFixtures\ORM;

use Symfony\Component\Yaml\Yaml;

use Doctrine\Common\Persistence\ObjectManager;

use Pim\Bundle\CatalogBundle\Entity\VariantGroup;
use Pim\Bundle\CatalogBundle\Entity\VariantGroupTranslation;
use Pim\Bundle\InstallerBundle\DataFixtures\ORM\AbstractInstallerFixture;

/**
 * Load fixtures for variant groups
 *
 * @author    Romain Monceau <romain@akeneo.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class LoadVariantGroupData extends AbstractInstallerFixture
{
    /**
     * {@inheritdoc}
     */
    public function load(ObjectManager $manager)
    {
        $configuration = Yaml::parse(realpath($this->getFilePath()));

        if (isset($configuration['variant_groups'])) {
            foreach ($configuration['variant_groups'] as $code => $data) {
                $variant = $this->createVariant($code, $data);
                $manager->persist($variant);
                $this->addReference('variant-group.'. $variant->getCode(), $variant);
            }
        }

        $manager->flush();
    }

    /**
     * Create a variant group entity
     *
     * @param string $code
     * @param array  $data
     *
     * @return \Pim\Bundle\CatalogBundle\Entity\VariantGroup
     */
    protected function createVariant($code, $data)
    {
        $variant = new VariantGroup();
        $variant->setCode($code);

        if (isset($data['labels'])) {
            foreach ($data['labels'] as $locale => $translation) {
                $this->createTranslation($variant, $locale, $translation);
            }
        }

        if (isset($data['attributes'])) {
            foreach ($data['attributes'] as $attribute) {
                $variant->addAttribute($this->getReference('product-attribute.'. $attribute));
            }
        }

        return $variant;
    }

    /**
     * Create a translation entity
     *
     * @param VariantGroup $variant
     * @param string       $locale
     * @param string       $content
     */
    protected function createTranslation($variant, $locale, $content)
    {
        $translation = new VariantGroupTranslation();
        $translation->setForeignKey($variant);
        $translation->setLocale($locale);
        $translation->setLabel($content);

        $variant->addTranslation($translation);
    }

    /**
     * {@inheritdoc}
     */
    public function getEntity()
    {
        return 'variant_groups';
    }

    /**
     * {@inheritdoc}
     */
    public function getOrder()
    {
        return 150;
    }
}
