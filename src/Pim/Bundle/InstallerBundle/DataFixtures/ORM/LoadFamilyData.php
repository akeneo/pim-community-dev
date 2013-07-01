<?php
namespace Pim\Bundle\InstallerBundle\DataFixtures\ORM;

use Doctrine\Common\Persistence\ObjectManager;
use Pim\Bundle\ProductBundle\Entity\Family;
use Pim\Bundle\ProductBundle\Entity\ProductAttribute;
use Pim\Bundle\ProductBundle\Entity\FamilyTranslation;
use Symfony\Component\Yaml\Yaml;

/**
 * Load fixtures for Product families
 *
 * @author    Filips Alpe <filips@akeneo.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 *
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
     * Create product family
     * @param string        $code         Product family code
     * @param array         $data  Product family attributes
     *
     * @return \Pim\Bundle\ProductBundle\Entity\Family
     */
    protected function createFamily($code, $data)
    {
        $family = new Family();
        $family->setCode($code);
        $family->setLabel($data['labels']['default']);

        foreach ($data['labels'] as $locale => $translation) {
            $this->createTranslation($family, $locale, $translation);
        }

        foreach ($data['attributes'] as $attribute) {
            $family->addAttribute($this->getReference('product-attribute.'.$attribute));
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
     * @return \Pim\Bundle\ProductBundle\Entity\FamilyTranslation
     */
    public function createTranslation($family, $locale, $content)
    {
        $translation = new FamilyTranslation();
        $translation->setContent($content);
        $translation->setField('label');
        $translation->setForeignKey($family);
        $translation->setLocale($locale);
        $translation->setObjectClass('Pim\Bundle\ProductBundle\Entity\Family');

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
