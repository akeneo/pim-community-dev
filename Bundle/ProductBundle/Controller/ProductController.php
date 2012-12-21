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
 * @license   http://opensource.org/licenses/MIT MIT
 *
 * @Route("/product")
 */
class ProductController extends Controller
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
     * @Route("/index")
     * @Template()
     *
     * @return multitype
     */
    public function indexAction()
    {
        // TODO : with lazy load
//        $products = $this->getProductManager()->getEntityRepository()->findAll();

        $products = $this->getProductManager()->getEntityRepository()->findByAttributes(array('name', 'size', 'description', 'color'));




/*
        $cnt = 0;
foreach ($products as $product) {
    var_dump($product->getValues());
exit();
}*/

        return array('products' => $products);
    }

    /**
     * @param integer $id
     *
     * @Route("/view/{id}")
     * @Template()
     *
     * @return multitype
     */
    public function viewAction($id)
    {
        $product = $this->getProductManager()->getEntityRepository()->find($id);

        return array('product' => $product);
    }

    /**
     * @Route("/insert")
     *
     * @return multitype
     */
    public function insertAction()
    {
        $messages = array();

        // get attributes
        $attName = $this->getProductManager()->getAttributeRepository()->findOneByCode('name');
        $attDescription = $this->getProductManager()->getAttributeRepository()->findOneByCode('description');
        $attSize = $this->getProductManager()->getAttributeRepository()->findOneByCode('size');
        $attColor = $this->getProductManager()->getAttributeRepository()->findOneByCode('color');

        for ($ind= 1; $ind < 100; $ind++) {

            // add product with only sku
            $prodSku = 'sku-'.$ind++;
            $newProduct = $this->getProductManager()->getEntityRepository()->findOneBySku($prodSku);
            if ($newProduct) {
                $messages[]= "Product ".$prodSku." already exists";
            } else {
                $newProduct = $this->getProductManager()->getNewEntityInstance();
                $newProduct->setSku($prodSku);
                $messages[]= "Product ".$prodSku." has been created";
                $this->getProductManager()->getStorageManager()->persist($newProduct);
            }

            // add product with sku and name and color
            $prodSku = 'sku-'.$ind++;
            $newProduct = $this->getProductManager()->getEntityRepository()->findOneBySku($prodSku);
            if ($newProduct) {
                $messages[]= "Product ".$prodSku." already exists";
            } else {
                $newProduct = $this->getProductManager()->getNewEntityInstance();
                $newProduct->setSku($prodSku);
                if ($attName) {
                    $valueName = $this->getProductManager()->getNewAttributeValueInstance();
                    $valueName->setAttribute($attName);
                    $valueName->setData('my name '.$ind);
                    $newProduct->addValue($valueName);
                }
                if ($attDescription) {
                    $value = $this->getProductManager()->getNewAttributeValueInstance();
                    $value->setAttribute($attDescription);
                    $value->setData('my long description '.$ind);
                    $newProduct->addValue($value);
                }
                if ($attColor) {
                    $value = $this->getProductManager()->getNewAttributeValueInstance();
                    $value->setAttribute($attColor);
                    $value->setData(1);
                    $newProduct->addValue($value);
                }
                $this->getProductManager()->getStorageManager()->persist($newProduct);
                $messages[]= "Product ".$prodSku." has been created";
            }

            // add product with sku, name and size
            $prodSku = 'sku-'.$ind;
            $newProduct = $this->getProductManager()->getEntityRepository()->findOneBySku($prodSku);
            if ($newProduct) {
                $messages[]= "Product ".$prodSku." already exists";
            } else {
                $newProduct = $this->getProductManager()->getNewEntityInstance();
                $newProduct->setSku($prodSku);
                if ($attName) {
                    $valueName = $this->getProductManager()->getNewAttributeValueInstance();
                    $valueName->setAttribute($attName);
                    $valueName->setData('my name '.$ind);
                    $newProduct->addValue($valueName);
                }
                if ($attSize) {
                    $valueSize = $this->getProductManager()->getNewAttributeValueInstance();
                    $valueSize->setAttribute($attSize);
                    $valueSize->setData(175);
                    $newProduct->addValue($valueSize);
                }
                $this->getProductManager()->getStorageManager()->persist($newProduct);
                $messages[]= "Product ".$prodSku." has been created";
            }
        }

        $this->getProductManager()->getStorageManager()->flush();

        $this->get('session')->setFlash('notice', implode(', ', $messages));

        return $this->redirect($this->generateUrl('oro_product_product_index'));
    }


    /**
     * @Route("/draft")
     * @Template()
     *
     * @return multitype
     */
    public function draftAction()
    {

        /*
        $product = new ProductEntity();
        $product->setSku('my-sku');

        $pm = $this->container->get('product_manager');
        $product = $pm->getNewEntityInstance();
        //var_dump($product);

        $products = $this->container->get('product_manager')->getEntityRepository()->findAll();
        foreach ($products as $product) {
            echo $product->getSku().' - '.$product->getname13558539042014().'<br/>';
        }
        echo '<br/>';

       // get default locale value
        $product = $pm->getEntityRepository()->find(2);
        echo $product->getSku().' - '.$product->getname13558539042014().'<br/>';

        // get french value
        foreach ($product->getValues() as $value) {
            $value->setTranslatableLocale('fr_FR');
            $pm->getStorageManager()->refresh($value);
        }
        echo $product->getSku().' - '.$product->getname13558539042014().'<br/>';
*/
  //      echo '<br/>';

        /*
        // query on default locale
        $em = $pm->getStorageManager();
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
        $em = $pm->getStorageManager();
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

        return array(/*'name' => $product->getSku()*/);
    }
}
