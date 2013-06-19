<?php

namespace Pim\Bundle\DemoBundle\DataFixtures\ORM;

use BeSimple\SoapCommon\Type\KeyValue\DateTime;

use Doctrine\Common\Collections\ArrayCollection;
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
        return $this->container->get('pim_product.manager.product');
    }

    /**
     * {@inheritdoc}
     */
    public function load(ObjectManager $manager)
    {
        $referencePrefix = 'product-attribute.';

        // sku
        $sku = $this->getProductManager()->createAttribute('pim_product_text');
        $sku->setCode('sku');
        $sku->setLabel('SKU');
        $sku->setRequired(true);
        $sku->setUseableAsGridColumn(true);
        $sku->setUseableAsGridFilter(true);
        $this->getProductManager()->getStorageManager()->persist($sku);
        $this->addReference($referencePrefix . $sku->getCode(), $sku);

        // attribute name
        $attributeCode = 'name';
        $attribute = $this->getProductManager()->createAttribute('pim_product_text');
        $attribute->setCode($attributeCode);
        $attribute->setLabel('Name');
        $attribute->setDescription(ucfirst($attributeCode .' description'));
        $attribute->setTranslatable(true);
        $attribute->setUseableAsGridColumn(true);
        $attribute->setUseableAsGridFilter(true);
        $this->createTranslation($attribute, 'default', 'label', 'Name');
        $this->createTranslation($attribute, 'en_US', 'label', 'Name');
        $this->createTranslation($attribute, 'fr_FR', 'label', 'Nom');
        $this->getProductManager()->getStorageManager()->persist($attribute);
        $this->addReference($referencePrefix. $attribute->getCode(), $attribute);

        // attribute price
        $attributeCode = 'price';
        $attribute = $this->getProductManager()->createAttribute('pim_product_price_collection');
        $attribute->setCode($attributeCode);
        $attribute->setLabel('Price');
        $attribute->setDescription(ucfirst($attributeCode .' description'));
        $attribute->setUseableAsGridColumn(true);
        $attribute->setUseableAsGridFilter(true);
        $attribute->setNumberMin(1);
        $attribute->setNumberMax(500);
        $this->createTranslation($attribute, 'default', 'label', 'Price');
        $this->createTranslation($attribute, 'en_US', 'label', 'Price');
        $this->createTranslation($attribute, 'fr_FR', 'label', 'Prix');
        $this->getProductManager()->getStorageManager()->persist($attribute);
        $this->addReference($referencePrefix. $attribute->getCode(), $attribute);

        // attribute short description
        $attributeCode = 'shortDescription';
        $attribute = $this->getProductManager()->createAttribute('pim_product_textarea');
        $attribute->setCode($attributeCode);
        $attribute->setLabel('Short Description');
        $attribute->setDescription(ucfirst($attributeCode .' description'));
        $attribute->setTranslatable(true);
        $attribute->setScopable(true);
        $attribute->setUseableAsGridColumn(true);
        $attribute->setUseableAsGridFilter(true);
        $attribute->setMaxCharacters(100);
        $this->createTranslation($attribute, 'default', 'label', 'Short description');
        $this->createTranslation($attribute, 'en_US', 'label', 'Short description');
        $this->createTranslation($attribute, 'fr_FR', 'label', 'Description courte');
        $this->getProductManager()->getStorageManager()->persist($attribute);
        $this->addReference($referencePrefix. $attribute->getCode(), $attribute);

        // attribute short description
        $attributeCode = 'longDescription';
        $attribute = $this->getProductManager()->createAttribute('pim_product_textarea');
        $attribute->setCode($attributeCode);
        $attribute->setLabel('Long Description');
        $attribute->setDescription(ucfirst($attributeCode .' description'));
        $attribute->setTranslatable(true);
        $attribute->setScopable(true);
        $attribute->setWysiwygEnabled(true);
        $this->createTranslation($attribute, 'default', 'label', 'Long description');
        $this->createTranslation($attribute, 'en_US', 'label', 'Long description');
        $this->createTranslation($attribute, 'fr_FR', 'label', 'Description longue');
        $this->getProductManager()->getStorageManager()->persist($attribute);
        $this->addReference($referencePrefix. $attribute->getCode(), $attribute);

        // attribute release date
        $attributeCode = 'releaseDate';
        $attribute = $this->getProductManager()->createAttribute('pim_product_date');
        $attribute->setCode($attributeCode);
        $attribute->setLabel('Release date');
        $attribute->setDescription(ucfirst($attributeCode .' description'));
        $attribute->setUseableAsGridColumn(true);
        $attribute->setUseableAsGridFilter(true);
        $attribute->setDateMin(new \DateTime('2012-01-01'));
        $attribute->setDateMax(new \DateTime('2014-01-01'));
        $this->createTranslation($attribute, 'default', 'label', 'Release date');
        $this->createTranslation($attribute, 'en_US', 'label', 'Release date');
        $this->createTranslation($attribute, 'fr_FR', 'label', 'Date de sortie');
        $this->getProductManager()->getStorageManager()->persist($attribute);
        $this->addReference($referencePrefix. $attribute->getCode(), $attribute);

        // attribute size
        $attributeCode = 'size';
        $attribute = $this->getProductManager()->createAttribute('pim_product_simpleselect');
        $attribute->setCode($attributeCode);
        $attribute->setLabel('Size');
        $attribute->setDescription(ucfirst($attributeCode .' description'));
        $attribute->setUseableAsGridColumn(true);
        $attribute->setUseableAsGridFilter(true);
        $sizes = array('XS', 'S', 'M', 'L', 'XL');
        foreach ($sizes as $size) {
            $option = $this->getProductManager()->createAttributeOption();
            $option->setTranslatable(true);
            $option->setDefaultValue($size);
            $attribute->addOption($option);
            $optionValue = $this->getProductManager()->createAttributeOptionValue();
            $optionValue->setLocale('en_US');
            $optionValue->setValue($size);
            $option->addOptionValue($optionValue);
        }
        $attribute->setDefaultValue($attribute->getOptions()->last());
        $this->createTranslation($attribute, 'default', 'label', 'Size');
        $this->createTranslation($attribute, 'en_US', 'label', 'Size');
        $this->createTranslation($attribute, 'fr_FR', 'label', 'Taille');
        $this->getProductManager()->getStorageManager()->persist($attribute);
        $this->addReference($referencePrefix. $attribute->getCode(), $attribute);

        // attribute color and translated options
        $attributeCode = 'color';
        $attribute = $this->getProductManager()->createAttribute('pim_product_multiselect');
        $attribute->setCode($attributeCode);
        $attribute->setLabel('Color');
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
            $option->setDefaultValue($color['en_US']);
            $attribute->addOption($option);
            foreach ($color as $locale => $translated) {
                $optionValue = $this->getProductManager()->createAttributeOptionValue();
                $optionValue->setValue($translated);
                $optionValue->setLocale($locale);
                $option->addOptionValue($optionValue);
            }
        }
        $defaultValues = new ArrayCollection();
        $defaultValues->add($attribute->getOptions()->first());
        $defaultValues->add($attribute->getOptions()->last());
        $attribute->setDefaultValue($defaultValues);
        $this->createTranslation($attribute, 'default', 'label', 'Color');
        $this->createTranslation($attribute, 'en_US', 'label', 'Color');
        $this->createTranslation($attribute, 'fr_FR', 'label', 'Couleur');
        $this->getProductManager()->getStorageManager()->persist($attribute);
        $this->addReference($referencePrefix. $attribute->getCode(), $attribute);

        // attribute manufacturer
        $attributeCode = 'manufacturer';
        $attribute = $this->getProductManager()->createAttribute('pim_product_simpleselect');
        $attribute->setCode($attributeCode);
        $attribute->setLabel('Manufacturer');
        $attribute->setDescription(ucfirst($attributeCode .' description'));
        $attribute->setUseableAsGridColumn(true);
        $attribute->setUseableAsGridFilter(true);
        $manufacturers = array('MyMug', 'MugStore');
        foreach ($manufacturers as $manufacturer) {
            $option = $this->getProductManager()->createAttributeOption();
            $option->setDefaultValue($manufacturer);
            $option->setTranslatable(true);
            $attribute->addOption($option);
            $optionValue = $this->getProductManager()->createAttributeOptionValue();
            $optionValue->setValue($manufacturer);
            $optionValue->setLocale('en_US');
            $option->addOptionValue($optionValue);
        }
        $attribute->setDefaultValue($attribute->getOptions()->last());
        $this->createTranslation($attribute, 'default', 'label', 'Manufacturer');
        $this->createTranslation($attribute, 'en_US', 'label', 'Manufacturer');
        $this->createTranslation($attribute, 'fr_FR', 'label', 'Fabricant');
        $this->getProductManager()->getStorageManager()->persist($attribute);
        $this->addReference($referencePrefix. $attribute->getCode(), $attribute);

        // attribute file upload
        $attributeCode = 'fileUpload';
        $attribute = $this->getProductManager()->createAttribute('pim_product_file');
        $attribute->setCode($attributeCode);
        $attribute->setLabel('File upload');
        $attribute->setDescription(ucfirst($attributeCode .' description'));
        $attribute->setUseableAsGridColumn(true);
        $attribute->setMaxFileSize(2000);
        $this->createTranslation($attribute, 'default', 'label', 'File upload');
        $this->createTranslation($attribute, 'en_US', 'label', 'File upload');
        $this->createTranslation($attribute, 'fr_FR', 'label', 'Fichier téléchargé');
        $this->getProductManager()->getStorageManager()->persist($attribute);
        $this->addReference($referencePrefix. $attribute->getCode(), $attribute);

        // attribute image upload
        $attributeCode = 'imageUpload';
        $attribute = $this->getProductManager()->createAttribute('pim_product_image');
        $attribute->setCode($attributeCode);
        $attribute->setLabel('Image upload');
        $attribute->setDescription(ucfirst($attributeCode .' description'));
        $attribute->setUseableAsGridColumn(true);
        $this->createTranslation($attribute, 'default', 'label', 'Image upload');
        $this->createTranslation($attribute, 'en_US', 'label', 'Image upload');
        $this->createTranslation($attribute, 'fr_FR', 'label', 'Image téléchargée');
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
     * @return \Pim\Bundle\ProductBundle\Entity\ProductAttributeTranslation
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
        return 20;
    }
}
