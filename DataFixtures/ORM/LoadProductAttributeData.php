<?php
namespace Pim\Bundle\DemoBundle\DataFixtures\ORM;

use Pim\Bundle\ProductBundle\Entity\ProductAttribute;

use Oro\Bundle\FlexibleEntityBundle\Model\AttributeType\OptionSimpleSelectType;

use Oro\Bundle\FlexibleEntityBundle\Model\AttributeType\MoneyType;

use Oro\Bundle\FlexibleEntityBundle\Model\AbstractAttributeType;

use Oro\Bundle\FlexibleEntityBundle\Model\AttributeType\OptionMultiCheckboxType;

use Oro\Bundle\FlexibleEntityBundle\Model\AttributeType\TextAreaType;

use Oro\Bundle\FlexibleEntityBundle\Model\AttributeType\DateType;

use Oro\Bundle\FlexibleEntityBundle\Model\AttributeType\TextType;

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
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 *
 */
class LoadProductAttributeData extends AbstractFixture implements OrderedFixtureInterface, ContainerAwareInterface
{

    /**
     * @var \Symfony\Component\DependencyInjection\ContainerInterface
     */
    protected $container;

    /**
     * Product manager
     * @var Oro\Bundle\FlexibleEntityBundle\Manager\FlexibleManager
     */
    protected $manager;

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
        // force in english
        $this->getProductManager()->setLocale('en_US');


        // create attribute
        $attribute = $this->createAttribute(new DateType(), 'releaseDate', true);

        // create specific attributes
        $attribute = $this->createAttribute(new TextAreaType(), 'shortDescription');
        $attribute->setTranslatable(true);
        $attribute->setScopable(true);
        $this->persist($attribute);


        $attribute = $this->createAttribute(new TextAreaType(), 'longDescription');
        $attribute->setTranslatable(true);
        $attribute->setScopable(true);
        $this->persist($attribute);


        // create size attribute
        $attribute = $this->createAttribute(new OptionSimpleSelectType(), 'size');
        // create options
        $sizes = array('XS', 'S', 'M', 'L', 'XL');
        foreach ($sizes as $size) {
            $option = $this->createOption($size);
            $attribute->addOption($option);
        }
        $this->persist($attribute);


        // attribute name (if not exists)
        $attribute = $this->createAttribute(new TextType(), 'name');
        $attribute->setTranslatable(true);
        $attribute->setRequired(true);
        $this->persist($attribute);


        // attribute price (if not exists)
        $attribute = $this->createAttribute(new MoneyType(), 'price');
        $this->persist($attribute);


        // attribute color (if not exists)
        $attribute = $this->createAttribute(new OptionMultiCheckboxType(), 'color');
        $attribute->setTranslatable(false); // only one value but option can be translated in option values

        // add translatable option and related value "Red", "Blue", "Green"
        $colors = array('Red', 'Blue', 'Orange', 'Yellow', 'Green', 'Black', 'White');
        foreach ($colors as $color) {
            $option = $this->getProductManager()->createAttributeOption();
            $option->setTranslatable(true);
            $optionValue = $this->getProductManager()->createAttributeOptionValue();
            $optionValue->setValue($color);
            $option->addOptionValue($optionValue);
            $attribute->addOption($option);
        }
        $this->persist($attribute);

        // flush
        $this->getProductManager()->getStorageManager()->flush();
    }

    /**
     * Persists entity and add it to references
     * @param ProductAttribute $attribute
     *
     * @throws \Exception
     */
    protected function persist(ProductAttribute $attribute)
    {
        $violationList = $this->container->get('validator')->validate($attribute);
        if ($violationList->count() === 0) {
            $this->getProductManager()->getStorageManager()->persist($attribute);
            $this->addReference('product-attribute.'. $attribute->getCode(), $attribute);
        } else {
            $errors = '';
            foreach ($violationList as $violation) {
                $errors .= $violation->getMessage() . PHP_EOL;
            }
            throw new \Exception('Error validating product attribute : '. $attribute->getCode() . PHP_EOL . $errors);
        }
    }

    /**
     * Create an option with values
     * @param string $name
     *
     * @return \Oro\Bundle\FlexibleEntityBundle\Model\AbstractAttributeOption
     */
    protected function createOption($name)
    {
        // create attribute option
        $option = $this->getProductManager()->createAttributeOption();
        $option->setTranslatable(true);

        // create option value
        $optionValue = $this->getProductManager()->createAttributeOptionValue();
        $optionValue->setValue($name);

        // add value to option
        $option->addOptionValue($optionValue);

        return $option;
    }

    /**
     * Create attribute
     * @param AbstractAttributeType $type    Attribute type
     * @param string                $code    Attribute code
     * @param boolean               $persist Direct persist entity ?
     *
     * @return \Pim\Bundle\ProductBundle\Entity\ProductAttribute
     */
    protected function createAttribute(AbstractAttributeType $type, $code, $persist = false)
    {
        // create extended attribute
        $attribute = $this->getProductManager()->createAttributeExtended($type);

        // set attribute values
        $attribute->setCode($code);

        // set extended attribute values
        $attribute->setName(ucfirst($code));
        $attribute->setDescription(ucfirst($code .' description'));
        $attribute->setVariant(0);

        // persist attribute
        if ($persist) {
            $this->persist($attribute);
        }

        return $attribute;
    }

    /**
     * {@inheritdoc}
     */
    public function getOrder()
    {
        return 2;
    }
}
