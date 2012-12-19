<?php

namespace Oro\Bundle\ProductBundle\Controller;

use Oro\Bundle\ProductBundle\Entity\ProductEntity;
use Oro\Bundle\DataModelBundle\Model\EntityAttribute as AbstractEntityAttribute;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;

/**
 * Default controller
 *
 * @author    Nicolas Dupont <nicolas@akeneo.com>
 * @copyright 2012 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 *
 * @Route("/default")
 */
class DefaultController extends Controller
{

    /**
     * Get product manager
     * @return FlexibleEntityManager
     */
    protected function getProductManager()
    {
        return $this->container->get('product_manager');
    }

    /**
     * @Route("/truncatedb")
     * @Template("OroProductBundle:Default:index.html.twig")
     */
    public function truncatedbAction()
    {
        // update schema / truncate db
        $em = $this->getProductManager()->getPersistenceManager();
        $metadatas = $em->getMetadataFactory()->getAllMetadata();
        if (!empty($metadatas)) {
            $tool = new \Doctrine\ORM\Tools\SchemaTool($em);
            $tool->dropSchema($metadatas);
            $tool->createSchema($metadatas);
        }

        $this->get('session')->setFlash('notice', "DB has been truncated with success (schema re-generation)");

        return array();
    }

    /**
     * @Route("/createattributes")
     * @Template("OroProductBundle:Default:index.html.twig")
     */
    public function createAttributesAction()
    {
        $messages = array();

        // attribute name (if not exists)
        $attNameCode = 'name';
        $attName = $this->getProductManager()->getAttributeRepository()->findOneByCode($attNameCode);
        if ($attName) {
            $messages[]= "Attribute ".$attNameCode." already exists";
        } else {
            $attName = $this->getProductManager()->getNewAttributeInstance();
            $attName->setCode($attNameCode);
            $attName->setTitle('Name');
            $attName->setType(AbstractEntityAttribute::TYPE_STRING);
            $attName->setTranslatable(true);
            $this->getProductManager()->getPersistenceManager()->persist($attName);
            $messages[]= "Attribute ".$attNameCode." has been created";
        }

        // attribute size (if not exists)
        $attSizeCode= 'size';
        $attSize = $this->getProductManager()->getAttributeRepository()->findOneByCode($attSizeCode);
        if ($attSize)  {
            $messages[]= "Attribute ".$attSizeCode." already exists";
        } else {
            $attSize = $this->getProductManager()->getNewAttributeInstance();
            $attSize->setCode($attSizeCode);
            $attSize->setTitle('Size');
            $attSize->setType(AbstractEntityAttribute::TYPE_NUMBER);
            $this->getProductManager()->getPersistenceManager()->persist($attSize);
            $messages[]= "Attribute ".$attSizeCode." has been created";
        }

        // translate attribute title in many locales in one time (saved when flush on entity manager)
        $repository = $this->getProductManager()->getPersistenceManager()->getRepository('Gedmo\\Translatable\\Entity\\Translation');
        $repository
            ->translate($attSize, 'title', 'de_De', 'size DE')
            ->translate($attSize, 'title', 'fr_FR', 'size FR')
            ->translate($attSize, 'title', 'es_ES', 'size ES');
        $messages[]= "Title of attribute ".$attSizeCode." has been translated in fr, de, es";

        $this->getProductManager()->getPersistenceManager()->flush();

        $this->get('session')->setFlash('notice', implode(', ', $messages));

        return array();
    }

    /**
     * @Route("/listattributes")
     * @Template("OroProductBundle:Default:attributes.html.twig")
     */
    public function listAttributesAction()
    {
        $attributes = $this->getProductManager()->getAttributeRepository()->findAll();

        return array('attributes' => $attributes);
    }

    /**
     * @Route("/createproducts")
     * @Template("OroProductBundle:Default:index.html.twig")
     */
    public function createProductsAction()
    {
        $messages = array();

        // get attributes
        $attName = $this->getProductManager()->getAttributeRepository()->findOneByCode('name');
        $attSize = $this->getProductManager()->getAttributeRepository()->findOneByCode('size');

        // add product with only sku
        $prodSku = 'sku-1';
        $newProduct = $this->getProductManager()->getEntityRepository()->findOneBySku($prodSku);
        if ($newProduct) {
            $messages[]= "Product ".$prodSku." already exists";
        } else {
            $newProduct = $this->getProductManager()->getNewEntityInstance();
            $newProduct->setSku($prodSku);
            $messages[]= "Product ".$prodSku." has been created";
            $this->getProductManager()->getPersistenceManager()->persist($newProduct);
        }

        // add product with sku and name
        $prodSku = 'sku-2';
        $newProduct = $this->getProductManager()->getEntityRepository()->findOneBySku($prodSku);
        if ($newProduct) {
            $messages[]= "Product ".$prodSku." already exists";
        } else {
            $newProduct = $this->getProductManager()->getNewEntityInstance();
            $newProduct->setSku($prodSku);
            if ($attName) {
                $valueName = $this->getProductManager()->getNewAttributeValueInstance();
                $valueName->setAttribute($attName);
                $valueName->setData('my name 2');
                $newProduct->addValue($valueName);
            }
            $this->getProductManager()->getPersistenceManager()->persist($newProduct);
            $messages[]= "Product ".$prodSku." has been created";
        }

        // add product with sku, name and size
        $prodSku = 'sku-3';
        $newProduct = $this->getProductManager()->getEntityRepository()->findOneBySku($prodSku);
        if ($newProduct) {
            $messages[]= "Product ".$prodSku." already exists";
        } else {
            $newProduct = $this->getProductManager()->getNewEntityInstance();
            $newProduct->setSku($prodSku);
            if ($attName) {
                $valueName = $this->getProductManager()->getNewAttributeValueInstance();
                $valueName->setAttribute($attName);
                $valueName->setData('my name 3');
                $newProduct->addValue($valueName);
            }
            if ($attSize) {
                $valueSize = $this->getProductManager()->getNewAttributeValueInstance();
                $valueSize->setAttribute($attSize);
                $valueSize->setData(175);
                $newProduct->addValue($valueSize);
            }
            $this->getProductManager()->getPersistenceManager()->persist($newProduct);
            $messages[]= "Product ".$prodSku." has been created";
        }

        $this->getProductManager()->getPersistenceManager()->flush();

        $this->get('session')->setFlash('notice', implode(', ', $messages));

        return array();
    }

    /**
     * @Route("/listproducts")
     * @Template("OroProductBundle:Default:products.html.twig")
     */
    public function listProductsAction()
    {
        $products = $this->getProductManager()->getEntityRepository()->findAll();

        return array('products' => $products);
    }

    /**
     * @Route("/index")
     * @Template()
     */
    public function indexAction()
    {
        $product = new ProductEntity();
        $product->setSku('my-sku');
/*
        $pm = $this->container->get('product_manager');
        $product = $pm->getNewEntityInstance();
        //var_dump($product);
*/
        $products = $this->container->get('product_manager')->getEntityRepository()->findAll();
        foreach ($products as $product) {
            echo $product->getSku().' - '.$product->getname13558539042014().'<br/>';
        }
        echo '<br/>';
/*
        // get default locale value
        $product = $pm->getEntityRepository()->find(2);
        echo $product->getSku().' - '.$product->getname13558539042014().'<br/>';

        // get french value
        foreach ($product->getValues() as $value) {
            $value->setTranslatableLocale('fr_FR');
            $pm->getPersistenceManager()->refresh($value);
        }
        echo $product->getSku().' - '.$product->getname13558539042014().'<br/>';
*/
        echo '<br/>';

        /*
        // query on default locale
        $em = $pm->getPersistenceManager();
        $em->clear();

        $query = $em->createQuery(
            'SELECT p FROM OroProductBundle:ProductEntity p WHERE p.sku = :sku '
        )->setParameter('sku', 'my sku 13558539042014');

//        $query = $em->createQuery('SELECT v FROM OroProductBundle:ProductAttributeValue v');
        $query->setHint(\Gedmo\Translatable\TranslatableListener::HINT_TRANSLATABLE_LOCALE, 'fr');


        $products = $query->getResult();
        foreach ($products as $product) {
            var_dump($product->getname13558539042014());
        }
        echo '<br/><br/>';

        // query on french locale
        $em = $pm->getPersistenceManager();
        $em->clear();

         $query = $em->createQuery(
                 'SELECT p FROM OroProductBundle:ProductEntity p WHERE p.sku = :sku '
         )->setParameter('sku', 'my sku 13558539042014');

        $query = $em->createQuery('SELECT v FROM OroProductBundle:ProductAttributeValue v');
        $query->setHint(\Gedmo\Translatable\TranslatableListener::HINT_TRANSLATABLE_LOCALE, 'en');

        // clear cache ??? see http://gediminasm.org/article/translatable-behavior-extension-for-doctrine-2
        $query->useResultCache(false);
        $query->expireQueryCache(true);


//        var_dump($query);

        //$query->useResultCache(false); // clear cache

        $values = $query->getArrayResult(); // array hydration$query->getResult();
        foreach ($values as $value) {
            var_dump($value);
        }
        echo '<br/>';

/*
        // fallback
        $query->setHint(
                \Gedmo\Translatable\TranslatableListener::HINT_FALLBACK,
                1, // fallback to default values in case if record is not translated
        );
*/

        return array('name' => $product->getSku());
    }
}
