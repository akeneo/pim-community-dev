<?php
namespace Pim\Bundle\DemoBundle\DataFixtures\ORM;

use Pim\Bundle\ProductBundle\Entity\ProductAttributeTranslation;
use Pim\Bundle\ProductBundle\Entity\ProductAttribute;
use Oro\Bundle\FlexibleEntityBundle\AttributeType\AbstractAttributeType;
use Doctrine\Common\Persistence\ObjectManager;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Doctrine\Common\DataFixtures\OrderedFixtureInterface;
use Doctrine\Common\DataFixtures\AbstractFixture;

/**
 * Load fixtures for Product attributes
 *
 * @author    Romain Monceau <romain@akeneo.com>
 * @copyright 2012 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 *
 */
class LoadProductAttributeData extends AbstractFixture implements OrderedFixtureInterface, ContainerAwareInterface
{

    /**
     * @var \Symfony\Component\DependencyInjection\ContainerInterface
     */
    protected $container;

    /**
     * {@inheritdoc}
     */
    public function setContainer(ContainerInterface $container = null)
    {
        $this->container = $container;
    }

    /**
     * Get entity manager
     * @return Oro\Bundle\FlexibleEntityBundle\Manager\FlexibleManager
     */
    protected function getProductManager()
    {
        return $this->container->get('product_manager');
    }

    /**
     * {@inheritdoc}
     */
    public function load(ObjectManager $manager)
    {
        $referencePrefix = 'product-attribute.';
        $attributeTypeFactory = $this->container->get('oro_flexibleentity.attributetype.factory');

        // attribute name
        $attributeCode = 'name';
        $attributeType = $attributeTypeFactory->get('oro_flexibleentity_text');
        $attribute = $this->getProductManager()->createAttribute($attributeType);
        $attribute->setCode($attributeCode);
        $attribute->setName('Name');
        $attribute->setDescription(ucfirst($attributeCode .' description'));
        $attribute->setTranslatable(true);
        $attribute->setUseableAsGridColumn(true);
        $attribute->setUseableAsGridFilter(true);
        $this->createTranslation($attribute, 'default', 'name', 'Name');
        $this->createTranslation($attribute, 'en_US', 'name', 'Name');
        $this->createTranslation($attribute, 'fr_FR', 'name', 'Nom');
        $this->getProductManager()->getStorageManager()->persist($attribute);
        $this->addReference($referencePrefix. $attribute->getCode(), $attribute);

        // attribute price
        $attributeCode = 'price';
        $attributeType = $attributeTypeFactory->get('oro_flexibleentity_money');
        $attribute = $this->getProductManager()->createAttribute($attributeType);
        $attribute->setCode($attributeCode);
        $attribute->setName('Price');
        $attribute->setDescription(ucfirst($attributeCode .' description'));
        $attribute->setUseableAsGridColumn(true);
        $attribute->setUseableAsGridFilter(true);
        $this->createTranslation($attribute, 'default', 'name', 'Price');
        $this->createTranslation($attribute, 'en_US', 'name', 'Price');
        $this->createTranslation($attribute, 'fr_FR', 'name', 'Prix');
        $this->getProductManager()->getStorageManager()->persist($attribute);
        $this->addReference($referencePrefix. $attribute->getCode(), $attribute);

        // attribute short description
        $attributeCode = 'shortDescription';
        $attributeType = $attributeTypeFactory->get('pim_product_textarea');
        $attribute = $this->getProductManager()->createAttribute($attributeType);
        $attribute->setCode($attributeCode);
        $attribute->setName('Short Description');
        $attribute->setDescription(ucfirst($attributeCode .' description'));
        $attribute->setTranslatable(true);
        $attribute->setScopable(true);
        $attribute->setUseableAsGridColumn(true);
        $attribute->setUseableAsGridFilter(true);
        $this->createTranslation($attribute, 'default', 'name', 'Short description');
        $this->createTranslation($attribute, 'en_US', 'name', 'Short description');
        $this->createTranslation($attribute, 'fr_FR', 'name', 'Description courte');
        $this->getProductManager()->getStorageManager()->persist($attribute);
        $this->addReference($referencePrefix. $attribute->getCode(), $attribute);

        // attribute short description
        $attributeCode = 'longDescription';
        $attributeType = $attributeTypeFactory->get('pim_product_textarea');
        $attribute = $this->getProductManager()->createAttribute($attributeType);
        $attribute->setCode($attributeCode);
        $attribute->setName('Long Description');
        $attribute->setDescription(ucfirst($attributeCode .' description'));
        $attribute->setTranslatable(true);
        $attribute->setScopable(true);
        $attribute->setWysiwygEnabled(true);
        $this->createTranslation($attribute, 'default', 'name', 'Long description');
        $this->createTranslation($attribute, 'en_US', 'name', 'Long description');
        $this->createTranslation($attribute, 'fr_FR', 'name', 'Description longue');
        $this->getProductManager()->getStorageManager()->persist($attribute);
        $this->addReference($referencePrefix. $attribute->getCode(), $attribute);

        // attribute release date
        $attributeCode = 'releaseDate';
        $attributeType = $attributeTypeFactory->get('oro_flexibleentity_date');
        $attribute = $this->getProductManager()->createAttribute($attributeType);
        $attribute->setCode($attributeCode);
        $attribute->setName('Release date');
        $attribute->setDescription(ucfirst($attributeCode .' description'));
        $attribute->setUseableAsGridColumn(true);
        $attribute->setUseableAsGridFilter(true);
        $this->createTranslation($attribute, 'default', 'name', 'Release date');
        $this->createTranslation($attribute, 'en_US', 'name', 'Release date');
        $this->createTranslation($attribute, 'fr_FR', 'name', 'Date de sortie');
        $this->getProductManager()->getStorageManager()->persist($attribute);
        $this->addReference($referencePrefix. $attribute->getCode(), $attribute);

        // attribute size
        $attributeCode = 'size';
        $attributeType = $attributeTypeFactory->get('pim_product_simpleselect');
        $attribute = $this->getProductManager()->createAttribute($attributeType);
        $attribute->setCode($attributeCode);
        $attribute->setName('Size');
        $attribute->setDescription(ucfirst($attributeCode .' description'));
        $attribute->setUseableAsGridColumn(true);
        $attribute->setUseableAsGridFilter(true);
        $sizes = array('XS', 'S', 'M', 'L', 'XL');
        foreach ($sizes as $size) {
            $option = $this->getProductManager()->createAttributeOption();
            $option->setTranslatable(true);
            $attribute->addOption($option);
            $optionValue = $this->getProductManager()->createAttributeOptionValue();
            $optionValue->setValue($size);
            $option->addOptionValue($optionValue);
        }
        $this->createTranslation($attribute, 'default', 'name', 'Size');
        $this->createTranslation($attribute, 'en_US', 'name', 'Size');
        $this->createTranslation($attribute, 'fr_FR', 'name', 'Taille');
        $this->getProductManager()->getStorageManager()->persist($attribute);
        $this->addReference($referencePrefix. $attribute->getCode(), $attribute);

        // attribute weight
        $attributeCode = 'weight';
        $attributeType = $attributeTypeFactory->get('oro_flexibleentity_metric');
        $attribute = $this->getProductManager()->createAttribute($attributeType);
        $attribute->setCode($attributeCode);
        $attribute->setName('Weight');
        $attribute->setDescription(ucfirst($attributeCode .' description'));
        $attribute->setUseableAsGridColumn(true);
        $this->createTranslation($attribute, 'default', 'name', 'Weight');
        $this->createTranslation($attribute, 'en_US', 'name', 'Weight');
        $this->createTranslation($attribute, 'fr_FR', 'name', 'Poids');
        $this->getProductManager()->getStorageManager()->persist($attribute);
        $this->addReference($referencePrefix. $attribute->getCode(), $attribute);

        // attribute color and translated options
        $attributeCode = 'color';
        $attributeType = $attributeTypeFactory->get('pim_product_multiselect');
        $attribute = $this->getProductManager()->createAttribute($attributeType);
        $attribute->setCode($attributeCode);
        $attribute->setName('Color');
        $attribute->setDescription(ucfirst($attributeCode .' description'));
        $attribute->setTranslatable(false); // only one value but option can be translated in option values
        $attribute->setUseableAsGridColumn(true);
        $attribute->setUseableAsGridFilter(true);
        $colors = array(
                array('en_US' => 'Red', 'fr_FR' => 'Rouge', 'de_DE' => 'Rot'),
                array('en_US' => 'Blue', 'fr_FR' => 'Bleu', 'de_DE' => 'Blau'),
                array('en_US' => 'Green', 'fr_FR' => 'Vert', 'de_DE' => 'Grün'),
                array('en_US' => 'Purple', 'fr_FR' => 'Violet', 'de_DE' => 'Lila'),
                array('en_US' => 'Orange', 'fr_FR' => 'Orange', 'de_DE' => 'Orange'),
        );
        foreach ($colors as $color) {
            $option = $this->getProductManager()->createAttributeOption();
            $option->setTranslatable(true);
            $attribute->addOption($option);
            foreach ($color as $locale => $translated) {
                $optionValue = $this->getProductManager()->createAttributeOptionValue();
                $optionValue->setValue($translated);
                $optionValue->setLocale($locale);
                $option->addOptionValue($optionValue);
            }
        }
        $this->createTranslation($attribute, 'default', 'name', 'Color');
        $this->createTranslation($attribute, 'en_US', 'name', 'Color');
        $this->createTranslation($attribute, 'fr_FR', 'name', 'Couleur');
        $this->getProductManager()->getStorageManager()->persist($attribute);
        $this->addReference($referencePrefix. $attribute->getCode(), $attribute);

        // attribute manufacturer
        $attributeCode = 'manufacturer';
        $attributeType = $attributeTypeFactory->get('pim_product_simpleselect');
        $attribute = $this->getProductManager()->createAttribute($attributeType);
        $attribute->setCode($attributeCode);
        $attribute->setName('Manufacturer');
        $attribute->setDescription(ucfirst($attributeCode .' description'));
        $attribute->setUseableAsGridColumn(true);
        $attribute->setUseableAsGridFilter(true);
        $manufacturers = array('MyMug', 'MugStore');
        foreach ($manufacturers as $manufacturer) {
            $option = $this->getProductManager()->createAttributeOption();
            $option->setTranslatable(true);
            $attribute->addOption($option);
            $optionValue = $this->getProductManager()->createAttributeOptionValue();
            $optionValue->setValue($manufacturer);
            $option->addOptionValue($optionValue);
        }
        $this->createTranslation($attribute, 'default', 'name', 'Manufacturer');
        $this->createTranslation($attribute, 'en_US', 'name', 'Manufacturer');
        $this->createTranslation($attribute, 'fr_FR', 'name', 'Fabricant');
        $this->getProductManager()->getStorageManager()->persist($attribute);
        $this->addReference($referencePrefix. $attribute->getCode(), $attribute);

        // attribute file upload
        $attributeCode = 'fileUpload';
        $attributeType = $attributeTypeFactory->get('oro_flexibleentity_file');
        $attribute = $this->getProductManager()->createAttribute($attributeType);
        $attribute->setCode($attributeCode);
        $attribute->setName('File upload');
        $attribute->setDescription(ucfirst($attributeCode .' description'));
        $attribute->setUseableAsGridColumn(true);
        $this->createTranslation($attribute, 'default', 'name', 'File upload');
        $this->createTranslation($attribute, 'en_US', 'name', 'File upload');
        $this->createTranslation($attribute, 'fr_FR', 'name', 'Fichier téléchargé');
        $this->getProductManager()->getStorageManager()->persist($attribute);
        $this->addReference($referencePrefix. $attribute->getCode(), $attribute);

        // attribute image upload
        $attributeCode = 'imageUpload';
        $attributeType = $attributeTypeFactory->get('oro_flexibleentity_image');
        $attribute = $this->getProductManager()->createAttribute($attributeType);
        $attribute->setCode($attributeCode);
        $attribute->setName('Image upload');
        $attribute->setDescription(ucfirst($attributeCode .' description'));
        $attribute->setUseableAsGridColumn(true);
        $this->createTranslation($attribute, 'default', 'name', 'Image upload');
        $this->createTranslation($attribute, 'en_US', 'name', 'Image upload');
        $this->createTranslation($attribute, 'fr_FR', 'name', 'Image téléchargée');
        $this->getProductManager()->getStorageManager()->persist($attribute);
        $this->addReference($referencePrefix. $attribute->getCode(), $attribute);

        $this->getProductManager()->getStorageManager()->flush();
    }

    /**
     * Create a translation entity
     *
     * @param ProductAttribute $attribute ProductAttribute entity
     * @param string           $locale    Locale used
     * @param string           $field     Field to translate
     * @param string           $content   Translated content
     *
     * @return \Pim\Bundle\ProductBundle\Entity\ProductSegmentTranslation
     */
    public function createTranslation($attribute, $locale, $field, $content)
    {
        $translation = new ProductAttributeTranslation();
        $translation->setContent($content);
        $translation->setField($field);
        $translation->setForeignKey($attribute);
        $translation->setLocale($locale);
        $translation->setObjectClass('Pim\Bundle\ProductBundle\Entity\ProductAttribute');

        $attribute->addTranslation($translation);
    }

    /**
     * {@inheritdoc}
     */
    public function getOrder()
    {
        return 2;
    }
}
