<?php
namespace Pim\Bundle\DemoBundle\DataFixtures\ORM;

use Pim\Bundle\ProductBundle\Entity\ProductAttribute;
use Oro\Bundle\FlexibleEntityBundle\Model\AttributeType\OptionSimpleSelectType;
use Oro\Bundle\FlexibleEntityBundle\Model\AttributeType\MoneyType;
use Oro\Bundle\FlexibleEntityBundle\Model\AbstractAttributeType;
use Oro\Bundle\FlexibleEntityBundle\Model\AttributeType\OptionMultiSelectType;
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
 * @author Romain Monceau <romain@akeneo.com>
 * @copyright 2012 Akeneo SAS (http://www.akeneo.com)
 * @license http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
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
        $productAttribute = $this->getProductManager()->createAttributeExtended(new TextType());
        $productAttribute->setCode($attributeCode);
        $productAttribute->setName('Name');
        $productAttribute->setDescription(ucfirst($attributeCode .' description'));
        $productAttribute->setTranslatable(true);
        $this->getProductManager()->getStorageManager()->persist($productAttribute);
        $this->addReference($referencePrefix. $productAttribute->getCode(), $productAttribute);

        // attribute price
        $attributeCode = 'price';
        $productAttribute = $this->getProductManager()->createAttributeExtended(new MoneyType());
        $productAttribute->setCode($attributeCode);
        $productAttribute->setName('Price');
        $productAttribute->setDescription(ucfirst($attributeCode .' description'));
        $this->getProductManager()->getStorageManager()->persist($productAttribute);
        $this->addReference($referencePrefix. $productAttribute->getCode(), $productAttribute);

        // attribute short description
        $attributeCode = 'shortDescription';
        $productAttribute = $this->getProductManager()->createAttributeExtended(new TextAreaType());
        $productAttribute->setCode($attributeCode);
        $productAttribute->setName('Short Description');
        $productAttribute->setDescription(ucfirst($attributeCode .' description'));
        $productAttribute->setTranslatable(true);
        $productAttribute->setScopable(true);
        $this->getProductManager()->getStorageManager()->persist($productAttribute);
        $this->addReference($referencePrefix. $productAttribute->getCode(), $productAttribute);

        // attribute short description
        $attributeCode = 'longDescription';
        $productAttribute = $this->getProductManager()->createAttributeExtended(new TextAreaType());
        $productAttribute->setCode($attributeCode);
        $productAttribute->setName('Long Description');
        $productAttribute->setDescription(ucfirst($attributeCode .' description'));
        $productAttribute->setTranslatable(true);
        $productAttribute->setScopable(true);
        $this->getProductManager()->getStorageManager()->persist($productAttribute);
        $this->addReference($referencePrefix. $productAttribute->getCode(), $productAttribute);

        // attribute relaease date
        $attributeCode = 'releaseDate';
        $productAttribute = $this->getProductManager()->createAttributeExtended(new DateType());
        $productAttribute->setCode($attributeCode);
        $productAttribute->setName('Release date');
        $productAttribute->setDescription(ucfirst($attributeCode .' description'));
        $this->getProductManager()->getStorageManager()->persist($productAttribute);
        $this->addReference($referencePrefix. $productAttribute->getCode(), $productAttribute);

        // attribute size
        $attributeCode = 'size';
        $productAttribute = $this->getProductManager()->createAttributeExtended(new OptionSimpleSelectType());
        $productAttribute->setCode($attributeCode);
        $productAttribute->setName('Size');
        $productAttribute->setDescription(ucfirst($attributeCode .' description'));
        $sizes = array('XS', 'S', 'M', 'L', 'XL');
        foreach ($sizes as $size) {
            $option = $this->getProductManager()->createAttributeOption();
            $option->setTranslatable(false);
            $productAttribute->addOption($option);
            $optionValue = $this->getProductManager()->createAttributeOptionValue();
            $optionValue->setValue($size);
            $option->addOptionValue($optionValue);
        }
        $this->getProductManager()->getStorageManager()->persist($productAttribute);
        $this->addReference($referencePrefix. $productAttribute->getCode(), $productAttribute);

        // attribute weight
        $attributeCode = 'weight';
        $productAttribute = $this->getProductManager()->createAttributeExtended(new MetricType());
        $productAttribute->setCode($attributeCode);
        $productAttribute->setName('Weight');
        $productAttribute->setDescription(ucfirst($attributeCode .' description'));
        $this->getProductManager()->getStorageManager()->persist($productAttribute);
        $this->addReference($referencePrefix. $productAttribute->getCode(), $productAttribute);

        // attribute color and translated options
        $attributeCode = 'color';
        $productAttribute = $this->getProductManager()->createAttributeExtended(new OptionMultiSelectType());
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
        $this->getProductManager()->getStorageManager()->persist($productAttribute);
        $this->addReference($referencePrefix. $productAttribute->getCode(), $productAttribute);

        // attribute manufacturer
        $attributeCode = 'manufacturer';
        $productAttribute = $this->getProductManager()->createAttributeExtended(new OptionSimpleSelectType());
        $productAttribute->setCode($attributeCode);
        $productAttribute->setName('Manufacturer');
        $productAttribute->setDescription(ucfirst($attributeCode .' description'));
        $manufacturers = array('MyMug', 'MugStore');
        foreach ($manufacturers as $manufacturer) {
            $option = $this->getProductManager()->createAttributeOption();
            $option->setTranslatable(false);
            $productAttribute->addOption($option);
            $optionValue = $this->getProductManager()->createAttributeOptionValue();
            $optionValue->setValue($manufacturer);
            $option->addOptionValue($optionValue);
        }
        $this->getProductManager()->getStorageManager()->persist($productAttribute);
        $this->addReference($referencePrefix. $productAttribute->getCode(), $productAttribute);

        // attribute file upload
        $attributeCode = 'fileUpload';
        $productAttribute = $this->getProductManager()->createAttributeExtended(new FileType());
        $productAttribute->setCode($attributeCode);
        $productAttribute->setName('File upload');
        $productAttribute->setDescription(ucfirst($attributeCode .' description'));
        $this->getProductManager()->getStorageManager()->persist($productAttribute);
        $this->addReference($referencePrefix. $productAttribute->getCode(), $productAttribute);

        $this->getProductManager()->getStorageManager()->flush();
    }

    /**
     * {@inheritdoc}
     */
    public function getOrder()
    {
        return 2;
    }
}