<?php
namespace Pim\Bundle\DemoBundle\DataFixtures\ORM;

use Pim\Bundle\ProductBundle\Entity\Product;
use Pim\Bundle\ProductBundle\Entity\ProductAttribute;
use Doctrine\Common\Persistence\ObjectManager;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Doctrine\Common\DataFixtures\OrderedFixtureInterface;
use Doctrine\Common\DataFixtures\AbstractFixture;
use Oro\Bundle\FlexibleEntityBundle\Entity\Price;
use Pim\Bundle\ProductBundle\Entity\ProductPrice;

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
     * @var \Symfony\Component\DependencyInjection\ContainerInterface
     */
    protected $container;

    /**
     * {@inheritDoc}
     */
    public function setContainer(ContainerInterface $container = null)
    {
        $this->container = $container;
    }

    /**
     * Get product manager
     * @return \Pim\Bundle\ProductBundle\Manager\ProductManager
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
        $nbProducts = 250;
        $batchSize = 500;

        // get scopes
        $scopeEcommerce = $this->getReference('channel.ecommerce');
        $scopeMobile    = $this->getReference('channel.mobile');

        // force in english because product is translatable
        $locale = $this->getReference('locale.en_US');
        $this->getProductManager()->setLocale($locale->getCode());

        // get currency
        $currencyUSD = $this->getReference('currency.USD');

        // get attributes by reference
        $attName        = $this->getReference('product-attribute.name');
        $attDate        = $this->getReference('product-attribute.releaseDate');
        $attDescription = $this->getReference('product-attribute.shortDescription');
        $attSize        = $this->getReference('product-attribute.size');
        $attLongDesc    = $this->getReference('product-attribute.longDescription');
        $attColor       = $this->getReference('product-attribute.color');
        $attPrice       = $this->getReference('product-attribute.price');
        $attManufact    = $this->getReference('product-attribute.manufacturer');
        $attImageUpload = $this->getReference('product-attribute.imageUpload');
        $attWeight      = $this->getReference('product-attribute.weight');

        // get attribute color options
        $optColors = $this->getProductManager()->getAttributeOptionRepository()->findBy(
            array('attribute' => $attColor)
        );
        $colors = array();
        foreach ($optColors as $option) {
            $colors[]= $option;
        }

        // get attribute size options
        $optSizes = $this->getProductManager()->getAttributeOptionRepository()->findBy(
            array('attribute' => $attSize)
        );
        $sizes = array();
        foreach ($optSizes as $option) {
            $sizes[]= $option;
        }

        // get attribute manufacturer options
        $optManufact = $this->getProductManager()->getAttributeOptionRepository()->findBy(
            array('attribute' => $attManufact)
        );
        $manufacturers = array();
        foreach ($optManufact as $option) {
            $manufacturers[]= $option;
        }

        $names = array('en_US' => 'my product name', 'fr_FR' => 'mon nom de produit', 'de_DE' => 'produkt namen');
        $descriptions = array('my long description', 'my other description');
        for ($ind= 0; $ind < $nbProducts; $ind++) {

            // sku
            $prodSku = 'sku-'.$ind;
            $product = $this->getProductManager()->createFlexible();
            $product->setSku($prodSku);

            // image upload
            $value = $this->getProductManager()->createFlexibleValue();
            $value->setAttribute($attImageUpload);
            $value->setData(null);
            $product->addValue($value);

            //weight
            $value = $this->getProductManager()->createFlexibleValue();
            $value->setAttribute($attWeight);
            $value->setData(null);
            $product->addValue($value);

            // product locales
            $product->addLocale($this->getReference('locale.de_DE'));
            $product->addLocale($this->getReference('locale.fr_FR'));
            $product->addLocale($this->getReference('locale.en_US'));

            // name
            foreach ($names as $locale => $data) {
                $value = $this->getProductManager()->createFlexibleValue();
                $value->setAttribute($attName);
                $value->setLocale($locale);
                $value->setData($data.' '.$ind);
                $product->addValue($value);
            }

            // short description
            $locales = array('en_US', 'fr_FR', 'de_DE');
            $scopes = array('ecommerce', 'mobile');
            foreach ($locales as $locale) {
                foreach ($scopes as $scope) {
                    $value = $this->getProductManager()->createFlexibleValue();
                    $value->setLocale($locale);
                    $value->setScope($scope);
                    $value->setAttribute($attDescription);
                    $product->addValue($value);
                    $value->setData('description ('.$locale.') ('.$scope.') '.$ind);
                }
            }

            // long description
            $locales = array('en_US', 'fr_FR', 'de_DE');
            $scopes = array('ecommerce', 'mobile');
            foreach ($locales as $locale) {
                foreach ($scopes as $scope) {
                    $value = $this->getProductManager()->createFlexibleValue();
                    $value->setLocale($locale);
                    $value->setScope($scope);
                    $value->setAttribute($attLongDesc);
                    $product->addValue($value);
                    $value->setData('long description ('.$locale.') ('.$scope.') '.$ind);
                }
            }

            // size
            $value = $this->getProductManager()->createFlexibleValue();
            $value->setAttribute($attSize);
            $firstSizeOpt = $sizes[rand(0, count($sizes)-1)];
            $value->setData($firstSizeOpt);
            $product->addValue($value);

            // manufacturer
            $value = $this->getProductManager()->createFlexibleValue();
            $value->setAttribute($attManufact);
            $firstManOpt = $manufacturers[rand(0, count($manufacturers)-1)];
            $value->setData($firstManOpt);
            $product->addValue($value);

            // color
            $value = $this->getProductManager()->createFlexibleValue();
            $value->setAttribute($attColor);
            $firstColorOpt = $colors[rand(0, count($colors)-1)];
            $value->addOption($firstColorOpt);
            $secondColorOpt = $colors[rand(0, count($colors)-1)];
            if ($firstColorOpt->getId() != $secondColorOpt->getId()) {
                $value->addOption($secondColorOpt);
            }
            $product->addValue($value);

            // price
            $value = $this->getProductManager()->createFlexibleValue();
            $value->setAttribute($attPrice);
            $currencies = array('USD', 'EUR');
            foreach ($currencies as $currency) {
                $price = new ProductPrice();
                $price->setData(rand(5, 100));
                $price->setCurrency($currency);
                $value->addPrice($price);
            }
            $product->addValue($value);

            // date
            $value = $this->getProductManager()->createFlexibleValue();
            $value->setAttribute($attDate);
            $value->setData(new \Datetime());
            $product->addValue($value);

            $this->persist($product);

            if ($ind % 20 === 0) {
                $family = $this->getReference('family.mug');
                $product->setProductFamily($family);
            } else if ($ind % 17 === 0) {
                $family = $this->getReference('family.shirt');
                $product->setProductFamily($family);
            }

            if (($ind % $batchSize) == 0) {
                $this->getProductManager()->getStorageManager()->flush();
            }
        }

        $this->getProductManager()->getStorageManager()->flush();
    }

    /**
     * Persist object and add it to references
     * @param Product $product
     */
    protected function persist(Product $product)
    {
        $this->getProductManager()->getStorageManager()->persist($product);
        $this->addReference('product-'. $product->getSku(), $product);
    }

    /**
     * {@inheritdoc}
     */
    public function getOrder()
    {
        return 40;
    }
}
