<?php

namespace Pim\Bundle\DemoBundle\DataFixtures\ORM;

use Doctrine\Common\Persistence\ObjectManager;
use Oro\Bundle\FlexibleEntityBundle\Entity\Price;
use Oro\Bundle\FlexibleEntityBundle\Entity\AttributeOption;
use Pim\Bundle\CatalogBundle\Entity\Channel;
use Pim\Bundle\CatalogBundle\Entity\Family;
use Pim\Bundle\CatalogBundle\Entity\ProductAttribute;
use Pim\Bundle\CatalogBundle\Model\ProductPrice;
use Pim\Bundle\CatalogBundle\Model\Product;

/**
 * Load products
 *
 * Execute with "php app/console doctrine:fixtures:load"
 *
 * @author    Nicolas Dupont <nicolas@akeneo.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 *
 */
class LoadProductData extends AbstractDemoFixture
{
    /**
     * @var AttributeOption[]
     */
    protected $colorOptions = null;

    /**
     * @var AttributeOption[]
     */
    protected $sizeOptions = null;

    /**
     * @var AttributeOption[]
     */
    protected $manufacturerOptions = null;

    /**
     * @var AttributeOption[]
     */
    protected $uniqueColorOptions = null;

    /**
     * Number of products to load
     * @staticvar int
     */
    const PRODUCT_COUNT = 100;

    /**
     * @staticvar int
     */
    const BATCH_SIZE = 200;

    /**
     * Get product manager
     * @return \Pim\Bundle\CatalogBundle\Manager\ProductManager
     */
    protected function getProductManager()
    {
        return $this->container->get('pim_catalog.manager.product');
    }

    /**
     * @return \Pim\Bundle\VersioningBundle\EventListener\AddVersionListener
     */
    protected function getAddVersionListener()
    {
        return $this->container->get('pim_versioning.event_listener.addversion');
    }

    /**
     * {@inheritDoc}
     */
    public function load(ObjectManager $manager)
    {
        if ($this->isEnabled() === false) {
            return;
        }

        $pm = $this->getProductManager();
        $this->getAddVersionListener()->setRealTimeVersioning(false);
        $channels = $this->getChannels();

        for ($ind= 0; $ind < self::PRODUCT_COUNT; $ind++) {

            $product = $pm->createFlexible();
            $product->setSku('sku-'.str_pad($ind, 3, '0', STR_PAD_LEFT));

            $family = $this->getRandomFamily();
            $product->setFamily($family);

            foreach ($channels as $channel) {
                $attributes = $this->getRandomAttributesToFulfill($family, $channel);
                foreach ($attributes as $attribute) {
                    $this->addValues($product, $attribute, $channel);
                }
            }

            $this->persist($product);

            if (($ind % self::BATCH_SIZE) == 0) {
                $pm->getStorageManager()->flush();
                $pm->getStorageManager()->clear('Pim\\Bundle\\CatalogBundle\\Model\\Product');
                $pm->getStorageManager()->clear('Pim\\Bundle\\CatalogBundle\\Model\\ProductValue');
                $pm->getStorageManager()->clear('Pim\\Bundle\\CatalogBundle\\Model\\ProductPrice');
                $pm->getStorageManager()->clear('Oro\\Bundle\\SearchBundle\\Entity\\Item');
                $pm->getStorageManager()->clear('Oro\\Bundle\\SearchBundle\\Entity\\IndexText');
            }
        }

        $pm->getStorageManager()->flush();
    }

    /**
     * @return Channel[]
     */
    protected function getChannels()
    {
        $channels = array();
        foreach (array('ecommerce', 'mobile') as $channelCode) {
            $channels[$channelCode] = $this->getReference('channel.'.$channelCode);
        }

        return $channels;
    }

    /**
     * @return Family
     */
    protected function getRandomFamily()
    {
        $families = array('mug', 'shirt', 'shoe', 'dress');
        $familyCode = $families[rand(0, count($families)-1)];

        return $this->getReference('attribute-family.'.$familyCode);
    }

    /**
     * @param Family  $family
     * @param Channel $channel
     *
     * @return string[]
     */
    protected function getRandomAttributesToFulfill(Family $family, Channel $channel)
    {
        $random = array();
        $attributes = $family->getAttributes();
        foreach ($attributes as $attribute) {
            if (rand(0, 1)) {
                $random[] = $attribute;
            }
        }

        return $random;
    }

    /**
     * @return AttributeOption[]
     */
    protected function getColorOptions()
    {
        if (!$this->colorOptions) {
            $attribute  = $this->getReference('product-attribute.color');
            $options = $this->getProductManager()->getAttributeOptionRepository()->findBy(
                array('attribute' => $attribute)
            );
            $this->colorOptions = $options;
        }

        return $this->colorOptions;
    }

    /**
     * @return AttributeOption[]
     */
    protected function getSizeOptions()
    {
        if (!$this->sizeOptions) {
            $attribute = $this->getReference('product-attribute.size');
            $options = $this->getProductManager()->getAttributeOptionRepository()->findBy(
                array('attribute' => $attribute)
            );
            $this->sizeOptions = $options;
        }

        return $this->sizeOptions;
    }

    /**
     * @return AttributeOption[]
     */
    protected function getManufacturerOptions()
    {
        if (!$this->manufacturerOptions) {
            $attribute = $this->getReference('product-attribute.manufacturer');
            $options = $this->getProductManager()->getAttributeOptionRepository()->findBy(
                array('attribute' => $attribute)
            );
            $this->manufacturerOptions = $options;
        }

        return $this->manufacturerOptions;
    }

    /**
     * @return AttributeOption[]
     */
    protected function getUniqueColorOptions()
    {
        if (!$this->uniqueColorOptions) {
            $attribute = $this->getReference('product-attribute.unique_color');
            $options = $this->getProductManager()->getAttributeOptionRepository()->findBy(
                array('attribute' => $attribute)
            );
            $this->uniqueColorOptions = $options;
        }

        return $this->uniqueColorOptions;
    }

    /**
     * Add values
     *
     * @param Product          $product
     * @param ProductAttribute $attribute
     * @param Channel          $channel
     */
    protected function addValues(Product $product, ProductAttribute $attribute, Channel $channel)
    {
        $generator  = \Faker\Factory::create();
        $scope      = $channel->getCode();
        $currencies = array('USD', 'EUR');

        if ($attribute->getCode() === 'name') {
            foreach ($channel->getLocales() as $locale) {
                if (!$product->getName($locale->getCode())) {
                    $product->setName($generator->sentence(3), $locale->getCode());
                }
            }

        } elseif ($attribute->getCode() === 'short_description') {
            foreach ($channel->getLocales() as $locale) {
                $product->setShortDescription($generator->sentence(5), $locale->getCode(), $scope);
            }

        } elseif ($attribute->getCode() === 'long_description') {
            foreach ($channel->getLocales() as $locale) {
                $product->setShortDescription($generator->sentence(5), $locale->getCode(), $scope);
            }

        } elseif ($attribute->getCode() === 'release_date') {
            if (!$product->getReleaseDate()) {
                $product->setReleaseDate($generator->dateTimeBetween("-1 year", "now"));
            }

        } elseif ($attribute->getCode() === 'price') {
            $prices = $product->getPrice();
            if (empty($prices)) {
                foreach ($currencies as $currency) {
                    $price = new ProductPrice($generator->randomFloat(2, 5, 100), $currency);
                    $product->addPrice($price);
                }
            }

        } elseif ($attribute->getCode() === 'color') {
            $colors = $product->getColor();
            if (empty($colors)) {
                $options = $this->getColorOptions();
                $firstOpt  = $options[rand(0, count($options)-1)];
                $secondOpt = $options[rand(0, count($options)-1)];
                $thirdOpt = $options[rand(0, count($options)-1)];
                $colors = array_unique(array($firstOpt, $secondOpt, $thirdOpt));
                $product->setColor($colors);
            }

        } elseif ($attribute->getCode() === 'size') {
            if (!$product->getSize()) {
                $options = $this->getSizeOptions();
                $option  = $options[rand(0, count($options)-1)];
                $product->setSize($option);
            }

        } elseif ($attribute->getCode() === 'manufacturer') {
            if (!$product->getManufacturer()) {
                $options = $this->getManufacturerOptions();
                $option  = $options[rand(0, count($options)-1)];
                $product->setManufacturer($option);
            }
        } elseif ($attribute->getCode() === 'unique_color') {
            $uniqueColors = $product->getUniqueColor();
            if (empty($uniqueColors)) {
                $options = $this->getUniqueColorOptions();
                $product->setUniqueColor($options[rand(0, count($options)-1)]);
            }
        }
    }

    /**
     * Persist object and add it to references
     * @param Product $product
     */
    protected function persist(Product $product)
    {
        $this->getProductManager()->getStorageManager()->persist($product);
        $this->addReference('product.'. $product->getSku(), $product);
    }

    /**
     * {@inheritdoc}
     */
    public function getOrder()
    {
        return 140;
    }
}
