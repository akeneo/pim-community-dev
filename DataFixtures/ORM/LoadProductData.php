<?php
namespace Pim\Bundle\DemoBundle\DataFixtures\ORM;

use Doctrine\Common\Collections\ArrayCollection;
use Oro\Bundle\DataAuditBundle\Entity\Audit;
use Oro\Bundle\UserBundle\Entity\User;
use Symfony\Component\Security\Core\Authentication\Token\UsernamePasswordToken;
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
class LoadProductData extends AbstractDemoFixture
{
    /**
     * Get product manager
     * @return \Pim\Bundle\ProductBundle\Manager\ProductManager
     */
    protected function getProductManager()
    {
        return $this->container->get('pim_product.manager.product');
    }

    /**
     * Get default admin user
     *
     * @return User
     */
    protected function getAdminUser()
    {
        $em = $this->container->get('doctrine.orm.entity_manager');
        $user = $em->getRepository('OroUserBundle:User')->findOneBy(array('username' => 'admin'));

        return $user;
    }

    /**
     * {@inheritDoc}
     */
    public function load(ObjectManager $manager)
    {
        if ($this->isEnabled() === false) {
            return;
        }

        $nbProducts = 250;
        $batchSize = 500;

        $generator = \Faker\Factory::create();

        // get locales, scopes, currencies
        $locales = array();
        foreach (array('en_US', 'fr_FR', 'de_DE') as $localeCode) {
            $locales[$localeCode] = $manager->getRepository('PimConfigBundle:Locale')->findOneBy(array('code' => $localeCode));
        }
        $scopes = array();
        foreach (array('ecommerce', 'mobile') as $scopeCode) {
            $scopes[$scopeCode] = $this->getReference('channel.'.$scopeCode);
        }
        $currencies = array('USD', 'EUR');

        // get attribute color options
        $attColor  = $this->getReference('product-attribute.color');
        $optColors = $this->getProductManager()->getAttributeOptionRepository()->findBy(
            array('attribute' => $attColor)
        );
        $colors = array();
        foreach ($optColors as $option) {
            $colors[]= $option;
        }

        // get attribute size options
        $attSize  = $this->getReference('product-attribute.size');
        $optSizes = $this->getProductManager()->getAttributeOptionRepository()->findBy(
            array('attribute' => $attSize)
        );
        $sizes = array();
        foreach ($optSizes as $option) {
            $sizes[]= $option;
        }

        // get attribute manufacturer options
        $attManufact = $this->getReference('product-attribute.manufacturer');
        $optManufact = $this->getProductManager()->getAttributeOptionRepository()->findBy(
            array('attribute' => $attManufact)
        );
        $manufacturers = array();
        foreach ($optManufact as $option) {
            $manufacturers[]= $option;
        }

        $names = array('en_US' => 'my product name', 'fr_FR' => 'mon nom de produit', 'de_DE' => 'produkt namen');
        for ($ind= 0; $ind < $nbProducts; $ind++) {

            $product = $this->getProductManager()->createFlexible();

            // enable the product on locales
            foreach ($locales as $locale) {
                $product->addLocale($locale);
            }

            // sku
            $prodSku = 'sku-'.str_pad($ind, 3, '0', STR_PAD_LEFT);
            $product->setSku($prodSku);

            // name
            foreach ($names as $locale => $data) {
                $product->setName($data.' '.$ind, $locale);
            }

            // short description
            foreach (array_keys($locales) as $locale) {
                foreach (array_keys($scopes) as $scope) {
                    $product->setShortDescription($generator->sentence(6), $locale, $scope);
                }
            }

            // long description
            foreach (array_keys($locales) as $locale) {
                foreach (array_keys($scopes) as $scope) {
                    $product->setLongDescription($generator->sentence(24), $locale, $scope);
                }
            }

            // date
            $product->setReleaseDate($generator->dateTimeBetween("-1 year", "now"));

            // size
            $firstSizeOpt = $sizes[rand(0, count($sizes)-1)];
            $product->setSize($firstSizeOpt);

            // manufacturer
            $firstManOpt = $manufacturers[rand(0, count($manufacturers)-1)];
            $product->setManufacturer($firstManOpt);

            // color
            $firstColorOpt = $colors[rand(0, count($colors)-1)];
            $product->addColor($firstColorOpt);
            $secondColorOpt = $colors[rand(0, count($colors)-1)];
            if ($firstColorOpt->getId() != $secondColorOpt->getId()) {
                $product->addColor($secondColorOpt);
            }

            // price
            foreach ($currencies as $currency) {
                $price = new ProductPrice($generator->randomFloat(2, 5, 100), $currency);
                $product->addPrice($price);
            }

            $this->persist($product);

            if ($ind % 3 === 0) {
                $family = $manager->getRepository('PimProductBundle:Family')->findOneBy(array('code' => 'mug'));
                $product->setFamily($family);
            } elseif ($ind % 7 === 0) {
                $family = $manager->getRepository('PimProductBundle:Family')->findOneBy(array('code' => 'shirt'));
                $product->setFamily($family);
            } elseif ($ind % 11 === 0) {
                $family = $manager->getRepository('PimProductBundle:Family')->findOneBy(array('code' => 'shoe'));
                $product->setFamily($family);
            }

            if (($ind % $batchSize) == 0) {
                $this->getProductManager()->getStorageManager()->flush();
            }
        }

        $this->getProductManager()->getStorageManager()->flush();

        // prepare user and data audit as user is not logged in
        $user = $this->getAdminUser();
        $products = $this->getProductManager()->getFlexibleRepository()->findAll();
        foreach ($products as $product) {
            $logEntry = new Audit();
            $logEntry->setAction('create');
            $logEntry->setObjectClass(get_class($product));
            $logEntry->setLoggedAt();
            $logEntry->setUser($user);
            $logEntry->setVersion(1);
            $logEntry->setObjectName(get_class($product));
            $logEntry->setObjectId($product->getId());
            $this->getProductManager()->getStorageManager()->persist($logEntry);
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
