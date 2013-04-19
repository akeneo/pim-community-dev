<?php
namespace Pim\Bundle\DemoBundle\DataFixtures\ORM;

use Pim\Bundle\ProductBundle\Entity\ProductAttributeTranslation;
use Oro\Bundle\FlexibleEntityBundle\Model\AttributeType\ImageType;
use Pim\Bundle\ProductBundle\Entity\ProductAttribute;
use Pim\Bundle\ProductBundle\Model\AttributeType\OptionSimpleSelectType;
use Pim\Bundle\ProductBundle\Model\AttributeType\OptionMultiSelectType;
use Oro\Bundle\FlexibleEntityBundle\Model\AttributeType\MoneyType;
use Oro\Bundle\FlexibleEntityBundle\Model\AbstractAttributeType;
use Oro\Bundle\FlexibleEntityBundle\Model\AttributeType\TextAreaType;
use Oro\Bundle\FlexibleEntityBundle\Model\AttributeType\DateType;
use Oro\Bundle\FlexibleEntityBundle\Model\AttributeType\TextType;
use Oro\Bundle\FlexibleEntityBundle\Model\AttributeType\MetricType;
use Oro\Bundle\FlexibleEntityBundle\Model\AttributeType\FileType;
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

        // attribute name
        $attributeCode = 'name';
        $productAttribute = $this->getProductManager()->createAttribute(new TextType());
        $productAttribute->setCode($attributeCode);
        $productAttribute->setName('Name');
        $productAttribute->setDescription(ucfirst($attributeCode .' description'));
        $productAttribute->setTranslatable(true);
        $this->createTranslation($productAttribute, 'default', 'name', 'Name');
        $this->createTranslation($productAttribute, 'en_US', 'name', 'Name');
        $this->createTranslation($productAttribute, 'fr_FR', 'name', 'Nom');
        $this->getProductManager()->getStorageManager()->persist($productAttribute);
        $this->addReference($referencePrefix. $productAttribute->getCode(), $productAttribute);

        // attribute price
        $attributeCode = 'price';
        $productAttribute = $this->getProductManager()->createAttribute(new MoneyType());
        $productAttribute->setCode($attributeCode);
        $productAttribute->setName('Price');
        $productAttribute->setDescription(ucfirst($attributeCode .' description'));
        $this->createTranslation($productAttribute, 'default', 'name', 'Price');
        $this->createTranslation($productAttribute, 'en_US', 'name', 'Price');
        $this->createTranslation($productAttribute, 'fr_FR', 'name', 'Prix');
        $this->getProductManager()->getStorageManager()->persist($productAttribute);
        $this->addReference($referencePrefix. $productAttribute->getCode(), $productAttribute);

        // attribute short description
        $attributeCode = 'shortDescription';
        $productAttribute = $this->getProductManager()->createAttribute(new TextAreaType());
        $productAttribute->setCode($attributeCode);
        $productAttribute->setName('Short Description');
        $productAttribute->setDescription(ucfirst($attributeCode .' description'));
        $productAttribute->setTranslatable(true);
        $productAttribute->setScopable(true);
        $this->createTranslation($productAttribute, 'default', 'name', 'Short description');
        $this->createTranslation($productAttribute, 'en_US', 'name', 'Short description');
        $this->createTranslation($productAttribute, 'fr_FR', 'name', 'Description courte');
        $this->getProductManager()->getStorageManager()->persist($productAttribute);
        $this->addReference($referencePrefix. $productAttribute->getCode(), $productAttribute);

        // attribute short description
        $attributeCode = 'longDescription';
        $productAttribute = $this->getProductManager()->createAttribute(new TextAreaType());
        $productAttribute->setCode($attributeCode);
        $productAttribute->setName('Long Description');
        $productAttribute->setDescription(ucfirst($attributeCode .' description'));
        $productAttribute->setTranslatable(true);
        $productAttribute->setScopable(true);
        $this->createTranslation($productAttribute, 'default', 'name', 'Long description');
        $this->createTranslation($productAttribute, 'en_US', 'name', 'Long description');
        $this->createTranslation($productAttribute, 'fr_FR', 'name', 'Description longue');
        $this->getProductManager()->getStorageManager()->persist($productAttribute);
        $this->addReference($referencePrefix. $productAttribute->getCode(), $productAttribute);

        // attribute relaease date
        $attributeCode = 'releaseDate';
        $productAttribute = $this->getProductManager()->createAttribute(new DateType());
        $productAttribute->setCode($attributeCode);
        $productAttribute->setName('Release date');
        $productAttribute->setDescription(ucfirst($attributeCode .' description'));
        $this->createTranslation($productAttribute, 'default', 'name', 'Release date');
        $this->createTranslation($productAttribute, 'en_US', 'name', 'Release date');
        $this->createTranslation($productAttribute, 'fr_FR', 'name', 'Date de sortie');
        $this->getProductManager()->getStorageManager()->persist($productAttribute);
        $this->addReference($referencePrefix. $productAttribute->getCode(), $productAttribute);

        // attribute size
        $attributeCode = 'size';
        $productAttribute = $this->getProductManager()->createAttribute(new OptionSimpleSelectType());
        $productAttribute->setCode($attributeCode);
        $productAttribute->setName('Size');
        $productAttribute->setDescription(ucfirst($attributeCode .' description'));
        $sizes = array('XS', 'S', 'M', 'L', 'XL');
        foreach ($sizes as $size) {
            $option = $this->getProductManager()->createAttributeOption();
            $option->setTranslatable(true);
            $productAttribute->addOption($option);
            $optionValue = $this->getProductManager()->createAttributeOptionValue();
            $optionValue->setValue($size);
            $option->addOptionValue($optionValue);
        }
        $this->createTranslation($productAttribute, 'default', 'name', 'Size');
        $this->createTranslation($productAttribute, 'en_US', 'name', 'Size');
        $this->createTranslation($productAttribute, 'fr_FR', 'name', 'Taille');
        $this->getProductManager()->getStorageManager()->persist($productAttribute);
        $this->addReference($referencePrefix. $productAttribute->getCode(), $productAttribute);

        // attribute weight
        $attributeCode = 'weight';
        $productAttribute = $this->getProductManager()->createAttribute(new MetricType());
        $productAttribute->setCode($attributeCode);
        $productAttribute->setName('Weight');
        $productAttribute->setDescription(ucfirst($attributeCode .' description'));
        $this->createTranslation($productAttribute, 'default', 'name', 'Weight');
        $this->createTranslation($productAttribute, 'en_US', 'name', 'Weight');
        $this->createTranslation($productAttribute, 'fr_FR', 'name', 'Poids');
        $this->getProductManager()->getStorageManager()->persist($productAttribute);
        $this->addReference($referencePrefix. $productAttribute->getCode(), $productAttribute);

        // attribute color and translated options
        $attributeCode = 'color';
        $productAttribute = $this->getProductManager()->createAttribute(new OptionMultiSelectType());
        $productAttribute->setCode($attributeCode);
        $productAttribute->setName('Color');
        $productAttribute->setDescription(ucfirst($attributeCode .' description'));
        $productAttribute->setTranslatable(false); // only one value but option can be translated in option values
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
            $productAttribute->addOption($option);
            foreach ($color as $locale => $translated) {
                $optionValue = $this->getProductManager()->createAttributeOptionValue();
                $optionValue->setValue($translated);
                $optionValue->setLocale($locale);
                $option->addOptionValue($optionValue);
            }
        }
        $this->createTranslation($productAttribute, 'default', 'name', 'Color');
        $this->createTranslation($productAttribute, 'en_US', 'name', 'Color');
        $this->createTranslation($productAttribute, 'fr_FR', 'name', 'Couleur');
        $this->getProductManager()->getStorageManager()->persist($productAttribute);
        $this->addReference($referencePrefix. $productAttribute->getCode(), $productAttribute);

        // attribute manufacturer
        $attributeCode = 'manufacturer';
        $productAttribute = $this->getProductManager()->createAttribute(new OptionSimpleSelectType());
        $productAttribute->setCode($attributeCode);
        $productAttribute->setName('Manufacturer');
        $productAttribute->setDescription(ucfirst($attributeCode .' description'));
        $manufacturers = array('MyMug', 'MugStore');
        foreach ($manufacturers as $manufacturer) {
            $option = $this->getProductManager()->createAttributeOption();
            $option->setTranslatable(true);
            $productAttribute->addOption($option);
            $optionValue = $this->getProductManager()->createAttributeOptionValue();
            $optionValue->setValue($manufacturer);
            $option->addOptionValue($optionValue);
        }
        $this->createTranslation($productAttribute, 'default', 'name', 'Manufacturer');
        $this->createTranslation($productAttribute, 'en_US', 'name', 'Manufacturer');
        $this->createTranslation($productAttribute, 'fr_FR', 'name', 'Fabricant');
        $this->getProductManager()->getStorageManager()->persist($productAttribute);
        $this->addReference($referencePrefix. $productAttribute->getCode(), $productAttribute);

        // attribute file upload
        $attributeCode = 'fileUpload';
        $productAttribute = $this->getProductManager()->createAttribute(new FileType());
        $productAttribute->setCode($attributeCode);
        $productAttribute->setName('File upload');
        $productAttribute->setDescription(ucfirst($attributeCode .' description'));
        $this->createTranslation($productAttribute, 'default', 'name', 'File upload');
        $this->createTranslation($productAttribute, 'en_US', 'name', 'File upload');
        $this->createTranslation($productAttribute, 'fr_FR', 'name', 'Fichier téléchargé');
        $this->getProductManager()->getStorageManager()->persist($productAttribute);
        $this->addReference($referencePrefix. $productAttribute->getCode(), $productAttribute);

        // attribute image upload
        $attributeCode = 'imageUpload';
        $productAttribute = $this->getProductManager()->createAttribute(new ImageType());
        $productAttribute->setCode($attributeCode);
        $productAttribute->setName('Image upload');
        $productAttribute->setDescription(ucfirst($attributeCode .' description'));
        $this->createTranslation($productAttribute, 'default', 'name', 'Image upload');
        $this->createTranslation($productAttribute, 'en_US', 'name', 'Image upload');
        $this->createTranslation($productAttribute, 'fr_FR', 'name', 'Image téléchargée');
        $this->getProductManager()->getStorageManager()->persist($productAttribute);
        $this->addReference($referencePrefix. $productAttribute->getCode(), $productAttribute);

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
