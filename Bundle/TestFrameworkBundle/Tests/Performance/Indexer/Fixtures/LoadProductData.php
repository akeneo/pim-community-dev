<?php
namespace Oro\Bundle\FlexibleEntityBundle\Tests\Performance;

use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Doctrine\Common\DataFixtures\AbstractFixture;
use Doctrine\Common\DataFixtures\OrderedFixtureInterface;
use Doctrine\Common\Persistence\ObjectManager;
use Oro\Bundle\FlexibleEntityBundle\Model\AbstractAttributeType;
use Acme\Bundle\DemoFlexibleEntityBundle\Entity\ProductAttribute;
use Oro\Bundle\FlexibleEntityBundle\Model\AttributeType\TextType;
use Oro\Bundle\FlexibleEntityBundle\Model\AttributeType\OptionMultiCheckboxType;
use Oro\Bundle\FlexibleEntityBundle\Model\AttributeType\MetricType;
use Oro\Bundle\FlexibleEntityBundle\Model\AttributeType\TextAreaType;
use Oro\Bundle\FlexibleEntityBundle\Model\AttributeType\PriceType;
use Oro\Bundle\FlexibleEntityBundle\Entity\Price;
use Oro\Bundle\FlexibleEntityBundle\Entity\Metric;

/**
* Load products
*
* Execute with "php app/console doctrine:fixtures:load"
*
*/
class LoadProductData extends AbstractFixture implements OrderedFixtureInterface, ContainerAwareInterface
{

    const DEFAULT_COUNTER_VALUE = 90;
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
     * Entities Counter
     * @var integer
     */
    protected $counter;

    /**
     * {@inheritDoc}
     */
    public function setContainer(ContainerInterface $container = null)
    {
        $this->container = $container;
        $this->setProductManager();
        if (isset($container->counter)) {
            $this->counter = $container->counter;
        } else {
            $this->counter = self::DEFAULT_COUNTER_VALUE;
        }
    }

    public function setProductManager()
    {
        $this->manager = $this->container->get('product_manager');
    }

    /**
     * Get product manager
     * @return FlexibleManager
     */
    protected function getProductManager()
    {
        return $this->manager;
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
     *
     * @return array
     */
    public function loadAttributes()
    {
        $messages = array();

        // force in english
        $this->getProductManager()->setLocale('en');

        // attribute name (if not exists)
        $attributeCode = 'name';
        $productAttribute = $this->getProductManager()->getFlexibleRepository()->findAttributeByCode($attributeCode);
        if (is_null($productAttribute)) {
            $productAttribute = $this->getProductManager()->createAttributeExtended(new TextType());
            $productAttribute->setSearchable(true);
            $productAttribute->setName('Name');
            $productAttribute->setCode($attributeCode);
            $productAttribute->setTranslatable(true);
            $productAttribute->setRequired(true);
            $this->getProductManager()->getStorageManager()->persist($productAttribute);
            $messages[]= "Attribute ".$attributeCode." has been created";
        }

        // attribute price (if not exists)
        $attributeCode = 'price';
        $productAttribute = $this->getProductManager()->getFlexibleRepository()->findAttributeByCode($attributeCode);
        if (is_null($productAttribute)) {
            $productAttribute = $this->getProductManager()->createAttributeExtended(new PriceType());
            $productAttribute->setSearchable(true);
            $productAttribute->setName('Price');
            $productAttribute->setCode($attributeCode);
            $this->getProductManager()->getStorageManager()->persist($productAttribute);
            $messages[]= "Attribute ".$attributeCode." has been created";
        }
        // attribute description (if not exists)
        $attributeCode = 'description';
        $productAttribute = $this->getProductManager()->getFlexibleRepository()->findAttributeByCode($attributeCode);
        if (is_null($productAttribute)) {
            $productAttribute = $this->getProductManager()->createAttributeExtended(new TextAreaType());
            $productAttribute->setSearchable(true);
            $productAttribute->setName('Description');
            $productAttribute->setCode($attributeCode);
            $productAttribute->setTranslatable(true);
            $productAttribute->setScopable(true);
            $this->getProductManager()->getStorageManager()->persist($productAttribute);
            $messages[]= "Attribute ".$attributeCode." has been created";
        }
        // attribute size (if not exists)
        $attributeCode= 'size';
        $productAttribute = $this->getProductManager()->getFlexibleRepository()->findAttributeByCode($attributeCode);
        if (is_null($productAttribute)) {
            $productAttribute = $this->getProductManager()->createAttributeExtended(new MetricType());
            $productAttribute->setSearchable(true);
            $productAttribute->setName('Size');
            $productAttribute->setCode($attributeCode);
            $this->getProductManager()->getStorageManager()->persist($productAttribute);
            $messages[]= "Attribute ".$attributeCode." has been created";
        }
        // attribute color (if not exists)
        $attributeCode= 'color';
        $productAttribute = $this->getProductManager()->getFlexibleRepository()->findAttributeByCode($attributeCode);
        if (is_null($productAttribute)) {
            $productAttribute = $this->getProductManager()->createAttributeExtended(new OptionMultiCheckboxType());
            $productAttribute->setSearchable(true);
            $productAttribute->setName('Color');
            $productAttribute->setCode($attributeCode);
            $productAttribute->setTranslatable(false); // only one value but option can be translated in option values
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
            $this->getProductManager()->getStorageManager()->persist($productAttribute);
            $messages[]= "Attribute ".$attributeCode." has been created";
        }
        $this->getProductManager()->getStorageManager()->flush();

        return $messages;
    }

    /**
     * Load products
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     * @SuppressWarnings(PHPMD.NPathComplexity)
     * TODO: this method should be refactored (BAP-975)
     *
     * @return array
     */
    public function loadProducts()
    {
        $messages = array();

        // force in english because product is translatable
        $this->getProductManager()->setLocale('en');

        // get attributes
        $attName = $this->getProductManager()->getFlexibleRepository()->findAttributeByCode('name');
        $attDescription = $this->getProductManager()->getFlexibleRepository()->findAttributeByCode('description');
        $attSize = $this->getProductManager()->getFlexibleRepository()->findAttributeByCode('size');
        $attColor = $this->getProductManager()->getFlexibleRepository()->findAttributeByCode('color');
        $attPrice = $this->getProductManager()->getFlexibleRepository()->findAttributeByCode('price');
        // get attribute color options
        $optColors = $this->getProductManager()->getAttributeOptionRepository()->findBy(
            array('attribute' => $attColor)
        );
        $colors = array();
        foreach ($optColors as $option) {
            $colors[]= $option;
        }

        $indSku = 1;
        $descriptions = array('my long description', 'my other description');
        for ($ind= 1; $ind <= $this->counter; $ind++) {
            list($msec, $sec) = explode(" ", microtime());
            $start=$sec + $msec;

            // add product with only sku and name
            $prodSku = 'perf-sku-' . $indSku;
            $product = $this->getProductManager()->createFlexible();
            $product->setSku($prodSku);
            if ($attName) {
                $value = $product->getValue($attName->getCode());
                if (!$value) {
                    $value = $this->getProductManager()->createFlexibleValue();
                    $value->setAttribute($attName);
                    $product->addValue($value);
                }
                $value->setData('my name '.$indSku);
            }
            $messages[]= "Product ".$prodSku." has been created";
            $this->getProductManager()->getStorageManager()->persist($product);
            $indSku++;

            // add product with sku, name, description, color and size
            $prodSku = 'perf-sku-' . $indSku;
            $product = $this->getProductManager()->createFlexible();
            $product->setSku($prodSku);
            if ($attName) {
                $value = $product->getValue($attName->getCode());
                if (!$value) {
                    $value = $this->getProductManager()->createFlexibleValue();
                    $value->setAttribute($attName);
                    $product->addValue($value);
                }
                $value->setData('my name '.$indSku);
            }
            if ($attDescription) {
                // scope ecommerce
                $value = $this->getProductManager()->createFlexibleValue();
                $value->setScope(ProductAttribute::SCOPE_ECOMMERCE);
                $value->setAttribute($attDescription);
                $myDescription = $descriptions[$ind%2];
                $value->setData($myDescription.'(ecommerce)');
                $product->addValue($value);
                // scope mobile
                $value = $this->getProductManager()->createFlexibleValue();
                $value->setScope(ProductAttribute::SCOPE_MOBILE);
                $value->setAttribute($attDescription);
                $value->setData($myDescription.'(mobile)');
                $product->addValue($value);
            }
            if ($attSize) {
                $value = $this->getProductManager()->createFlexibleValue();
                $value->setAttribute($attSize);
                $value->setData(175); // single value
                $value->setUnit('mm');
                $product->addValue($value);
            }
            if ($attColor) {
                $value = $this->getProductManager()->createFlexibleValue();
                $value->setAttribute($attColor);
                // pick many colors (multiselect)
                $firstColorOpt = $colors[rand(0, count($colors)-1)];
                $value->addOption($firstColorOpt);
                $secondColorOpt = $colors[rand(0, count($colors)-1)];
                if ($firstColorOpt->getId() != $secondColorOpt->getId()) {
                    $value->addOption($secondColorOpt);
                }
                $product->addValue($value);
            }
            $this->getProductManager()->getStorageManager()->persist($product);
            $messages[]= "Product ".$prodSku." has been created";
            $indSku++;

            // add product with sku, name, size and price
            $prodSku = 'perf-sku-'.$indSku;
            $product = $this->getProductManager()->createFlexible();
            $product->setSku($prodSku);
            if ($attName) {
                $value = $product->getValue($attName->getCode());
                if (!$value) {
                    $value = $this->getProductManager()->createFlexibleValue();
                    $value->setAttribute($attName);
                    $product->addValue($value);
                }
                $value->setData('my name '.$indSku);
            }
            if ($attSize) {
                $value = $this->getProductManager()->createFlexibleValue();
                $value->setAttribute($attSize);
                $metric = new Metric();
                $metric->setUnit('mm');
                $metric->setData(rand(5, 100));
                $value->setData($metric);
                $product->addValue($value);
            }
            if ($attPrice) {
                $value = $this->getProductManager()->createFlexibleValue();
                $value->setAttribute($attPrice);
                $price = new Price();
                $price->setData(rand(5, 100));
                $price->setCurrency('USD');
                $value->setData($price);
                $product->addValue($value);
            }
            $this->getProductManager()->getStorageManager()->persist($product);
            $messages[]= "Product ".$prodSku." has been created";
            $indSku++;

            if (!($ind % 100)) {
                list($msec, $sec) = explode(" ", microtime());
                $stop=$sec + $msec;

                echo "\nGenerated {$ind} entities " . round($stop - $start, 4) . " sec";
            }
        }
        list($msec, $sec) = explode(" ", microtime());
        $start=$sec + $msec;

        echo "\nFlushing";
        $this->getProductManager()->getStorageManager()->flush();
        list($msec, $sec) = explode(" ", microtime());
        $stop=$sec + $msec;

        echo "\nFlushed " . round($stop - $start, 4) . " sec";

        return $messages;
    }

    /**
     * Load translated data
     *
     * @return array
     */
    public function loadTranslations()
    {
        // get attributes
        $attName = $this->getProductManager()->getFlexibleRepository()->findAttributeByCode('name');
        $attDescription = $this->getProductManager()->getFlexibleRepository()->findAttributeByCode('description');

        // get products
        $products = $this->getProductManager()->getFlexibleRepository()->findByWithAttributes();
        $ind = 1;
        foreach ($products as $product) {
            // translate name value
            if ($attName) {
                if ($product->setLocale('en')->getValue('name') != null) {
                    $value = $this->getProductManager()->createFlexibleValue();
                    $value->setAttribute($attName);
                    $value->setLocale('fr');
                    $value->setData('mon nom FR '.$ind);
                    $product->addValue($value);
                    $this->getProductManager()->getStorageManager()->persist($value);
                    $messages[]= "Value 'name' has been translated";
                }
            }
            // translate description value
            if ($attDescription) {
                // check if a value en + scope ecommerce exists
                if ($product->setLocale('en')->setScope('ecommerce')->getValue('description') != null) {
                    // scope ecommerce
                    $value = $this->getProductManager()->createFlexibleValue();
                    $value->setLocale('fr');
                    $value->setScope(ProductAttribute::SCOPE_ECOMMERCE);
                    $value->setAttribute($attDescription);
                    $value->setData('ma description FR (ecommerce) '.$ind);
                    $product->addValue($value);
                    $this->getProductManager()->getStorageManager()->persist($value);
                    // scope mobile
                    $value = $this->getProductManager()->createFlexibleValue();
                    $value->setLocale('fr');
                    $value->setScope(ProductAttribute::SCOPE_MOBILE);
                    $value->setAttribute($attDescription);
                    $value->setData('ma description FR (mobile) '.$ind);
                    $product->addValue($value);
                    $this->getProductManager()->getStorageManager()->persist($value);
                    $messages[]= "Value 'description' has been translated";
                }
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
                $optValueFr->setLocale('fr');
                $option->addOptionValue($optValueFr);
                $this->getProductManager()->getStorageManager()->persist($optValueFr);
                $messages[]= "Option '".$colorEn."' has been translated";
            }
        }

        $this->getProductManager()->getStorageManager()->flush();

        return $messages;
    }

    /**
     * {@inheritdoc}
     */
    public function getOrder()
    {
        return 3;
    }

    /**
     * Generate firstname
     * @return string
     */
    protected function generateFirstname()
    {
        $listFirstname = array('Performance', 'Load');
        $random = rand(0, count($listFirstname)-1);

        return $listFirstname[$random];
    }

    /**
     * Generate lastname
     * @return string
     */
    protected function generateLastname()
    {
        $listLastname = array('Quality', 'Tester');
        $random = rand(0, count($listLastname)-1);

        return $listLastname[$random];
    }

    /**
     * Generate birthdate
     * @return string
     */
    protected function generateBirthDate()
    {
        $year  = rand(1980, 2000);
        $month = rand(1, 12);
        $day   = rand(1, 28);

        return $year .'-'. $month .'-'. $day;
    }
}
