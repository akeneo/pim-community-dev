<?php
namespace Pim\Bundle\DemoBundle\DataFixtures\ORM\Product;

use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Doctrine\Common\DataFixtures\AbstractFixture;
use Doctrine\Common\DataFixtures\OrderedFixtureInterface;
use Doctrine\Common\Persistence\ObjectManager;
use Oro\Bundle\FlexibleEntityBundle\Model\AbstractAttributeType;
use Pim\Bundle\ProductBundle\Entity\ProductAttribute;
use Oro\Bundle\FlexibleEntityBundle\Model\AttributeType\TextType;
use Oro\Bundle\FlexibleEntityBundle\Model\AttributeType\OptionMultiCheckboxType;
use Oro\Bundle\FlexibleEntityBundle\Model\AttributeType\MetricType;
use Oro\Bundle\FlexibleEntityBundle\Model\AttributeType\TextAreaType;
use Oro\Bundle\FlexibleEntityBundle\Model\AttributeType\MoneyType;

/**
* Load products
*
* Execute with "php app/console doctrine:fixtures:load"
*
 * @author    Nicolas Dupont <nicolas@akeneo.com>
 * @copyright 2012 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
*
*/
class LoadProductData extends AbstractFixture implements OrderedFixtureInterface, ContainerAwareInterface
{

    /**
     * @var ContainerInterface
     */
    protected $container;

    /**
     * Flexible entity manager
     * @var FlexibleManager
     */
    protected $manager;

    /**
     * {@inheritDoc}
     */
    public function setContainer(ContainerInterface $container = null)
    {
        $this->container = $container;
    }

    /**
     * Get product manager
     * @return \Oro\Bundle\FlexibleEntityBundle\Manager\FlexibleManager
     */
    protected function getProductManager()
    {
        return $this->container->get('product_manager');
    }

    /**
     * {@inheritDoc}
     */
    public function load(ObjectManager $manager)
    {
        $this->loadAttributes();
        $this->loadProducts();
        $this->loadTranslations();
    }

    /**
     * Load attributes
     */
    public function loadAttributes()
    {
        // force in english
        $this->getProductManager()->setLocale('en_US');

        // attribute name (if not exists)
        $attributeCode = 'name';
        $productAttribute = $this->getProductManager()->createAttributeExtended(new TextType());
        $productAttribute->setName('Name');
        $productAttribute->setCode($attributeCode);
        $productAttribute->setTranslatable(true);
        $productAttribute->setRequired(true);
        $productAttribute->setVariant(0);

        // persists and add to references
        $this->getProductManager()->getStorageManager()->persist($productAttribute);
        $this->addReference('product-attribute.'. $attributeCode, $productAttribute);


        // attribute price (if not exists)
        $attributeCode = 'price';
        $productAttribute = $this->getProductManager()->createAttributeExtended(new MoneyType());
        $productAttribute->setName('Price');
        $productAttribute->setCode($attributeCode);
        $productAttribute->setVariant(0);

        // persists and add to references
        $this->getProductManager()->getStorageManager()->persist($productAttribute);
        $this->addReference('product-attribute.'. $attributeCode, $productAttribute);


        // attribute description (if not exists)
        $attributeCode = 'description';
        $productAttribute = $this->getProductManager()->createAttributeExtended(new TextAreaType());
        $productAttribute->setName('Description');
        $productAttribute->setCode($attributeCode);
        $productAttribute->setTranslatable(true);
        $productAttribute->setScopable(true);
        $productAttribute->setVariant(0);

        // persists and add to references
        $this->getProductManager()->getStorageManager()->persist($productAttribute);
        $this->addReference('product-attribute.'. $attributeCode, $productAttribute);


        // attribute size (if not exists)
        $attributeCode= 'size';
        $productAttribute = $this->getProductManager()->createAttributeExtended(new MetricType());
        $productAttribute->setName('Size');
        $productAttribute->setCode($attributeCode);
        $productAttribute->setVariant(0);

        // persists and add to references
        $this->getProductManager()->getStorageManager()->persist($productAttribute);
        $this->addReference('product-attribute.'. $attributeCode, $productAttribute);


        // attribute color (if not exists)
        $attributeCode= 'color';
        $productAttribute = $this->getProductManager()->createAttributeExtended(new OptionMultiCheckboxType());
        $productAttribute->setName('Color');
        $productAttribute->setCode($attributeCode);
        $productAttribute->setTranslatable(false); // only one value but option can be translated in option values
        $productAttribute->setVariant(0);

        // add translatable option and related value "Red", "Blue", "Green"
        $colors = array("Red", "Blue", "Green");
        foreach ($colors as $color) {
            $option = $this->getProductManager()->createAttributeOption();
            $option->setTranslatable(true);
            $optionValue = $this->getProductManager()->createAttributeOptionValue();
            $optionValue->setValue($color);
            $option->addOptionValue($optionValue);
            $productAttribute->addOption($option);
        }

        // persists and add to references
        $this->getProductManager()->getStorageManager()->persist($productAttribute);
        $this->addReference('product-attribute.'. $attributeCode, $productAttribute);


        $this->getProductManager()->getStorageManager()->flush();
    }

    /**
     * Load products
     */
    public function loadProducts()
    {
        // force in english because product is translatable
        $this->getProductManager()->setLocale('en_US');

        // get attributes by reference
        $attName        = $this->getReference('product-attribute.name');
        $attDescription = $this->getReference('product-attribute.description');
        $attSize        = $this->getReference('product-attribute.size');
        $attColor       = $this->getReference('product-attribute.color');
        $attPrice       = $this->getReference('product-attribute.price');

        // get attribute color options
        $optColors = $this->getProductManager()->getAttributeOptionRepository()->findBy(
            array('attribute' => $attColor->getAttribute())
        );
        $colors = array();
        foreach ($optColors as $option) {
            $colors[] = $option;
        }

        $indSku = 0;
        $descriptions = array('my long description', 'my other description');
        for ($ind= 1; $ind <= 3; $ind++) {
            // add product with only sku and name
            $prodSku = 'sku-'.++$indSku;
            $product = $this->getProductManager()->createFlexible();
            $product->setSku($prodSku);

            // Set attribute name value
            $value = $product->getValue($attName->getCode());
            $value->setData('my name '.$indSku);
            $this->getProductManager()->getStorageManager()->persist($product);


            // add product with sku, name, description, color and size
            $prodSku = 'sku-'.++$indSku;
            $product = $this->getProductManager()->createFlexible();
            $product->setSku($prodSku);

            // set name value
            $value = $product->getValue($attName->getCode());
            $value->setData('my name '.$indSku);

            // set description value by scope
            // scope ecommerce
            $value = $this->getProductManager()->createFlexibleValue();
            $value->setScope(ProductAttribute::SCOPE_ECOMMERCE);
            $value->setAttribute($attDescription->getAttribute());
            $myDescription = $descriptions[$ind%2];
            $value->setData($myDescription.'(ecommerce)');
            $product->addValue($value);
            // scope mobile
            $value = $this->getProductManager()->createFlexibleValue();
            $value->setScope(ProductAttribute::SCOPE_MOBILE);
            $value->setAttribute($attDescription->getAttribute());
            $value->setData($myDescription.'(mobile)');
            $product->addValue($value);


            // set attribute size value
            $value = $this->getProductManager()->createFlexibleValue();
            $value->setAttribute($attSize->getAttribute());
            $value->setData(175);
            $value->setUnit('mm');
            $product->addValue($value);


            // set attribute color value
            $value = $this->getProductManager()->createFlexibleValue();
            $value->setAttribute($attColor->getAttribute());
            // pick many colors (multiselect)
            $firstColorOpt = $colors[rand(0, count($colors)-1)];
            $value->addOption($firstColorOpt);
            $secondColorOpt = $colors[rand(0, count($colors)-1)];
            if ($firstColorOpt->getId() != $secondColorOpt->getId()) {
                $value->addOption($secondColorOpt);
            }
            $product->addValue($value);

            // persists
            $this->getProductManager()->getStorageManager()->persist($product);


            // add product with sku, name, size and price
            $prodSku = 'sku-'.++$indSku;
            $product = $this->getProductManager()->createFlexible();
            $product->setSku($prodSku);

            // set attribute name value
            $value = $product->getValue($attName->getCode());
            $value->setData('my name '.$indSku);

            // set attribute size value
            $value = $this->getProductManager()->createFlexibleValue();
            $value->setAttribute($attSize->getAttribute());
            $value->setData(175);
            $value->setUnit('mm');
            $product->addValue($value);

            // set attribute price value
            $value = $this->getProductManager()->createFlexibleValue();
            $value->setAttribute($attPrice->getAttribute());
            $value->setData(rand(5, 100));
            $value->setCurrency('USD');
            $product->addValue($value);

            // persists
            $this->getProductManager()->getStorageManager()->persist($product);
        }

        $this->getProductManager()->getStorageManager()->flush();
    }

    /**
     * Load translated data
     */
    public function loadTranslations()
    {
        // get attributes
        $attName        = $this->getReference('product-attribute.name');
        $attDescription = $this->getReference('product-attribute.description');

        // get products
        $products = $this->getProductManager()->getFlexibleRepository()->findByWithAttributes();
        $ind = 1;
        foreach ($products as $product) {
            // translate name value
            if ($product->setLocale('en_US')->getValue('name') != null) {
                $value = $this->getProductManager()->createFlexibleValue();
                $value->setAttribute($attName->getAttribute());
                $value->setLocale('fr_FR');
                $value->setData('mon nom FR '.$ind);
                $product->addValue($value);
                $this->getProductManager()->getStorageManager()->persist($value);
            }

            // translate description value

            // check if a value en_US + scope ecommerce exists
            if ($product->setLocale('en_US')->setScope('ecommerce')->getValue('description') != null) {
                // scope ecommerce
                $value = $this->getProductManager()->createFlexibleValue();
                $value->setLocale('fr_FR');
                $value->setScope(ProductAttribute::SCOPE_ECOMMERCE);
                $value->setAttribute($attDescription->getAttribute());
                $value->setData('ma description FR (ecommerce) '.$ind);
                $product->addValue($value);
                $this->getProductManager()->getStorageManager()->persist($value);

                // scope mobile
                $value = $this->getProductManager()->createFlexibleValue();
                $value->setLocale('fr_FR');
                $value->setScope(ProductAttribute::SCOPE_MOBILE);
                $value->setAttribute($attDescription->getAttribute());
                $value->setData('ma description FR (mobile) '.$ind);
                $product->addValue($value);
                $this->getProductManager()->getStorageManager()->persist($value);
            }

            $ind++;
        }

        // get color attribute options
        $attColor = $this->getProductManager()->getFlexibleRepository()->findAttributeByCode('color');
        $colors = array("Red" => "Rouge", "Blue" => "Bleu", "Green" => "Vert");
        // translate
        foreach ($colors as $colorEn => $colorFr) {
            $optValueEn = $this->getProductManager()->getAttributeOptionValueRepository()->findOneBy(
                array('value' => $colorEn)
            );
            $optValueFr = $this->getProductManager()->getAttributeOptionValueRepository()->findOneBy(
                array('value' => $colorFr)
            );

            if ($optValueEn and !$optValueFr) {
                $option = $optValueEn->getOption();
                $optValueFr = $this->getProductManager()->createAttributeOptionValue();
                $optValueFr->setValue($colorFr);
                $optValueFr->setLocale('fr_FR');
                $option->addOptionValue($optValueFr);
                $this->getProductManager()->getStorageManager()->persist($optValueFr);
            }
        }

        $this->getProductManager()->getStorageManager()->flush();
    }

    /**
     * {@inheritdoc}
     */
    public function getOrder()
    {
        return 3;
    }
}
