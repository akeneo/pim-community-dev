<?php

namespace Pim\Bundle\InstallerBundle\DataFixtures\ORM;

use Pim\Bundle\CatalogBundle\Entity\AttributeRequirement;

use Symfony\Component\Yaml\Yaml;
use Doctrine\Common\Persistence\ObjectManager;
use Pim\Bundle\CatalogBundle\Entity\Family;
use Pim\Bundle\CatalogBundle\Entity\FamilyTranslation;

/**
 * Load fixtures for families
 *
 * @author    Filips Alpe <filips@akeneo.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class LoadFamilyData extends AbstractInstallerFixture
{
    /**
     * {@inheritdoc}
     */
    public function load(ObjectManager $manager)
    {
        $configuration = Yaml::parse(realpath($this->getFilePath()));

        if (isset($configuration['families'])) {
            foreach ($configuration['families'] as $code => $data) {
                $family = $this->createFamily($code, $data);
                $manager->persist($family);
                $this->addReference('attribute-family.'.$family->getCode(), $family);
            }
        }

        $manager->flush();
    }

    /**
     * Create a family
     * @param string $code
     * @param array  $data
     *
     * @return Family
     */
    protected function createFamily($code, $data)
    {
        $family = new Family();
        $family->setCode($code);

        foreach ($data['labels'] as $locale => $translation) {
            $this->createTranslation($family, $locale, $translation);
        }

        foreach ($data['attributes'] as $attribute) {
            $family->addAttribute($this->getReference('product-attribute.'.$attribute));
        }

        if (array_key_exists('requirements', $data)) {
            foreach ($data['requirements'] as $channel => $attributes) {
                foreach ($attributes as $attribute) {
                    $requirement =  new AttributeRequirement();
                    $requirement->setAttribute($this->getReference('product-attribute.'.$attribute));
                    $requirement->setChannel($this->getReference('channel.'.$channel));
                    $requirement->setRequired(true);
                    $family->addAttributeRequirement($requirement);
                }
            }
        }

        if (isset($data['attributeAsLabel'])) {
            $family->setAttributeAsLabel($this->getReference('product-attribute.'.$data['attributeAsLabel']));
        }

        return $family;
    }

    /**
     * Create a translation entity
     *
     * @param Family $family  entity
     * @param string $locale  Locale used
     * @param string $content Translated content
     *
     * @return \Pim\Bundle\CatalogBundle\Entity\FamilyTranslation
     */
    public function createTranslation($family, $locale, $content)
    {
        $translation = new FamilyTranslation();
        $translation->setForeignKey($family);
        $translation->setLocale($locale);
        $translation->setLabel($content);

        $family->addTranslation($translation);
    }

    /**
     * {@inheritdoc}
     */
    public function getEntity()
    {
        return 'families';
    }

    /**
     * {@inheritdoc}
     */
    public function getOrder()
    {
        return 30;
    }
}
