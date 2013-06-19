<?php

namespace Pim\Bundle\DemoBundle\DataFixtures\ORM;

use Doctrine\Common\Persistence\ObjectManager;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Doctrine\Common\DataFixtures\OrderedFixtureInterface;
use Doctrine\Common\DataFixtures\AbstractFixture;
use Pim\Bundle\ProductBundle\Entity\ProductFamily;
use Pim\Bundle\ProductBundle\Entity\ProductAttribute;
use Pim\Bundle\ProductBundle\Entity\ProductFamilyTranslation;

/**
 * Load fixtures for Product families
 *
 * @author    Filips Alpe <filips@akeneo.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 *
 */
class LoadProductFamilyData extends AbstractFixture implements OrderedFixtureInterface
{

    /**
     * {@inheritdoc}
     */
    public function load(ObjectManager $manager)
    {
        return false;
        $attributes = array(
            $this->getReference('product-attribute.name'),
            $this->getReference('product-attribute.manufacturer'),
            $this->getReference('product-attribute.shortDescription'),
            $this->getReference('product-attribute.color')
        );
        $translations = array('default' => 'Mug (default)', 'en_US' => 'Mug', 'fr_FR' => 'Tasse');
        $family = $this->createProductFamily('mug', 'Mug', $attributes, $manager, $translations);

        $attributes = array(
            $this->getReference('product-attribute.name'),
            $this->getReference('product-attribute.size'),
            $this->getReference('product-attribute.shortDescription'),
            $this->getReference('product-attribute.color')
        );
        $translations = array('default' => 'Shirt (default)', 'en_US' => 'Shirt', 'fr_FR' => 'Chemise');
        $this->createProductFamily('shirt', 'Chemise', $attributes, $manager, $translations);
    }

    /**
     * Create product family
     * @param string        $code         Product family code
     * @param string        $label        Product family name
     * @param array         $attributes   Product family attributes
     * @param ObjectManager $manager      EntityManager
     * @param array         $translations Label translation
     *
     * @return \Pim\Bundle\ProductBundle\Entity\ProductFamily
     */
    protected function createProductFamily($code, $label, $attributes, $manager, $translations)
    {
        $family = new ProductFamily();

        $family->setCode($code);
        $family->setLabel($label);

        foreach ($attributes as $attribute) {
            $family->addAttribute($attribute);
        }

        foreach ($translations as $locale => $translation) {
            $this->createTranslation($family, $locale, 'label', $translation);
        }


        $manager->persist($family);
        $manager->flush();

        $this->setReference('family.'. $code, $family);

        return $family;
    }

    /**
     * Create a translation entity
     *
     * @param ProductFamily $family  entity
     * @param string        $locale  Locale used
     * @param string        $field   Field to translate
     * @param string        $content Translated content
     *
     * @return \Pim\Bundle\ProductBundle\Entity\ProductAttributeTranslation
     */
    public function createTranslation($family, $locale, $field, $content)
    {
        $translation = new ProductFamilyTranslation();
        $translation->setContent($content);
        $translation->setField($field);
        $translation->setForeignKey($family);
        $translation->setLocale($locale);
        $translation->setObjectClass('Pim\Bundle\ProductBundle\Entity\ProductFamily');

        $family->addTranslation($translation);
    }

    /**
     * {@inheritdoc}
     */
    public function getOrder()
    {
        return 30;
    }
}
