<?php
namespace Oro\Bundle\ProductBundle\Controller;

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
     * Get attribute codes
     * @return array
     */
    protected function getAttributeCodesToDisplay()
    {
        return array('name', 'description', 'size', 'color');
    }

    /**
     * @Route("/index")
     * @Template()
     *
     * @return multitype
     */
    public function indexAction()
    {
        $products = $this->getProductManager()->getEntityRepository()->findByWithAttributes();

        return array('products' => $products, 'attributes' => $this->getAttributeCodesToDisplay());
    }

    /**
     * @Route("/querylazyload")
     * @Template("OroProductBundle:Product:index.html.twig")
     *
     * @return multitype
     */
    public function querylazyloadAction()
    {
        // get only entities, values and attributes are lazy loaded
        // you can use any criteria, order you want it's a classic doctrine query
        $products = $this->getProductManager()->getEntityRepository()->findBy(array());

        return array('products' => $products, 'attributes' => $this->getAttributeCodesToDisplay());
    }

    /**
     * @Route("/queryonlyname")
     * @Template("OroProductBundle:Product:index.html.twig")
     *
     * @return multitype
     */
    public function queryonlynameAction()
    {
        // get all entity fields and directly get attributes values
        $products = $this->getProductManager()->getEntityRepository()->findByWithAttributes(array('name'));

        return array('products' => $products, 'attributes' => array('name'));
    }

    /**
     * @Route("/querynameanddesc")
     * @Template("OroProductBundle:Product:index.html.twig")
     *
     * @return multitype
     */
    public function querynameanddescAction()
    {
        // get all entity fields and directly get attributes values
        $products = $this->getProductManager()->getEntityRepository()->findByWithAttributes(array('name', 'description'));

        return array('products' => $products, 'attributes' => array('name', 'description'));
    }

    /**
     * @Route("/queryfilterskufield")
     * @Template("OroProductBundle:Product:index.html.twig")
     *
     * @return multitype
     */
    public function queryfilterskufieldAction()
    {
        // get all entity fields, directly get attributes values, filter on entity field value
        $products = $this->getProductManager()->getEntityRepository()->findByWithAttributes(array(), array('sku' => 'sku-2'));

        return array('products' => $products, 'attributes' => array());
    }

    /**
     * @Route("/querynamefilterskufield")
     * @Template("OroProductBundle:Product:index.html.twig")
     *
     * @return multitype
     */
    public function querynamefilterskufieldAction()
    {
        // get all entity fields, directly get attributes values, filter on entity field value
        $products = $this->getProductManager()->getEntityRepository()->findByWithAttributes(array('name'), array('sku' => 'sku-2'));

        return array('products' => $products, 'attributes' => array('name'));
    }

    /**
     * @Route("/queryfiltersizeattribute")
     * @Template("OroProductBundle:Product:index.html.twig")
     *
     * @return multitype
     */
    public function queryfiltersizeattributeAction()
    {
        // get all entity fields, directly get attributes values, filter on attribute value
        $products = $this->getProductManager()->getEntityRepository()->findByWithAttributes(array('description', 'size'), array('size' => 175));

        return array('products' => $products, 'attributes' => array('description', 'size'));
    }

    /**
     * @Route("/queryfiltersizeanddescattributes")
     * @Template("OroProductBundle:Product:index.html.twig")
     *
     * @return multitype
     */
    public function queryfiltersizeanddescattributesAction()
    {
        // get all entity fields, directly get attributes values, filter on attribute value
        $products = $this->getProductManager()->getEntityRepository()->findByWithAttributes(
            array('size', 'description'),
            array('size' => 175, 'description' => 'my other description')
        );

        return array('products' => $products, 'attributes' => array('description', 'size'));
    }

    /**
     * @Route("/querynameanddesclimit")
     * @Template("OroProductBundle:Product:index.html.twig")
     *
     * @return multitype
     */
    public function querynameanddesclimitAction()
    {
        // get all entity fields and directly get attributes values
        $products = $this->getProductManager()->getEntityRepository()->findByWithAttributes(array('name', 'description'), null, null, 10, 1);

        return array('products' => $products, 'attributes' => array('name', 'description'));
    }

    /**
     * @Route("/querynameanddescorderby")
     * @Template("OroProductBundle:Product:index.html.twig")
     *
     * @return multitype
     */
    public function querynameanddescorderbyAction()
    {
        // get all entity fields and directly get attributes values
        $products = $this->getProductManager()->getEntityRepository()->findByWithAttributes(
            array('name', 'description'), null, array('description' => 'desc', 'id' => 'asc')
        );

        return array('products' => $products, 'attributes' => array('name', 'description'));
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
        // with lazy loading
        //$product = $this->getProductManager()->getEntityRepository()->find($id);
        // with any values
        $product = $this->getProductManager()->getEntityRepository()->findWithAttributes($id);

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

        // force in english because product is translatable
        $this->getProductManager()->setLocaleCode('en');

        // get attributes
        $attName = $this->getProductManager()->getAttributeRepository()->findOneByCode('name');
        $attDescription = $this->getProductManager()->getAttributeRepository()->findOneByCode('description');
        $attSize = $this->getProductManager()->getAttributeRepository()->findOneByCode('size');
        $attColor = $this->getProductManager()->getAttributeRepository()->findOneByCode('color');
        // get first attribute option
        $optColor = $this->getProductManager()->getAttributeOptionRepository()->findOneBy(array('attribute' => $attColor));

        $indSku = 1;
        $descriptions = array('my long descrition', 'my other description');
        for ($ind= 1; $ind <= 33; $ind++) {

            // add product with only sku
            $prodSku = 'sku-'.$indSku;
            $newProduct = $this->getProductManager()->getEntityRepository()->findOneBySku($prodSku);
            if ($newProduct) {
                $messages[]= "Product ".$prodSku." already exists";
            } else {
                $newProduct = $this->getProductManager()->createEntity();
                $newProduct->setSku($prodSku);
                $messages[]= "Product ".$prodSku." has been created";
                $this->getProductManager()->getStorageManager()->persist($newProduct);
                $indSku++;
            }

            // add product with sku, name, description, color and size
            $prodSku = 'sku-'.$indSku;
            $newProduct = $this->getProductManager()->getEntityRepository()->findOneBySku($prodSku);
            if ($newProduct) {
                $messages[]= "Product ".$prodSku." already exists";
            } else {
                $newProduct = $this->getProductManager()->createEntity();
                $newProduct->setSku($prodSku);
                if ($attName) {
                    $valueName = $this->getProductManager()->createEntityValue();
                    $valueName->setAttribute($attName);
                    $valueName->setData('my name '.$indSku);
                    $newProduct->addValue($valueName);
                }
                if ($attDescription) {
                    $value = $this->getProductManager()->createEntityValue();
                    $value->setAttribute($attDescription);
                    $value->setData($descriptions[$ind%2]);
                    $newProduct->addValue($value);
                }
                if ($attSize) {
                    $valueSize = $this->getProductManager()->createEntityValue();
                    $valueSize->setAttribute($attSize);
                    $valueSize->setData(175);
                    $newProduct->addValue($valueSize);
                }
                if ($attColor) {
                    $value = $this->getProductManager()->createEntityValue();
                    $value->setAttribute($attColor);
                    $value->setData($optColor); // we set option as data, you can use $value->setOption($optColor) too
                    $newProduct->addValue($value);
                }
                $this->getProductManager()->getStorageManager()->persist($newProduct);
                $messages[]= "Product ".$prodSku." has been created";
                $indSku++;
            }

            // add product with sku, name and size
            $prodSku = 'sku-'.$indSku;
            $newProduct = $this->getProductManager()->getEntityRepository()->findOneBySku($prodSku);
            if ($newProduct) {
                $messages[]= "Product ".$prodSku." already exists";
            } else {
                $newProduct = $this->getProductManager()->createEntity();
                $newProduct->setSku($prodSku);
                if ($attName) {
                    $valueName = $this->getProductManager()->createEntityValue();
                    $valueName->setAttribute($attName);
                    $valueName->setData('my name '.$indSku);
                    $newProduct->addValue($valueName);
                }
                if ($attSize) {
                    $valueSize = $this->getProductManager()->createEntityValue();
                    $valueSize->setAttribute($attSize);
                    $valueSize->setData(175);
                    $newProduct->addValue($valueSize);
                }
                $this->getProductManager()->getStorageManager()->persist($newProduct);
                $messages[]= "Product ".$prodSku." has been created";
                $indSku++;
            }
        }

        $this->getProductManager()->getStorageManager()->flush();

        $this->get('session')->setFlash('notice', implode(', ', $messages));

        return $this->redirect($this->generateUrl('oro_product_product_index'));
    }

    /**
     * @Route("/translate")
     *
     * @return multitype
     */
    public function translateAction()
    {
        $messages = array();

        // force in english
        $this->getProductManager()->setLocaleCode('en');

        // get attributes
        $attName = $this->getProductManager()->getAttributeRepository()->findOneByCode('name');
        $attDescription = $this->getProductManager()->getAttributeRepository()->findOneByCode('description');

        // get products
        $products = $this->getProductManager()->getEntityRepository()->findByWithAttributes();
        $ind = 1;
        foreach ($products as $product) {
            // translate name value
            if ($attName) {
                if ($product->setLocaleCode('en')->getValue('name') != null) {
                    $value = $this->getProductManager()->createEntityValue();
                    $value->setAttribute($attName);
                    $value->setLocaleCode('fr');
                    $value->setData('mon nom FR '.$ind++);
                    $product->addValue($value);
                    $this->getProductManager()->getStorageManager()->persist($value);
                    $messages[]= "Value 'name' has been translated";
                }
            }
            // translate description value
            if ($attDescription) {
                if ($product->getValue('description') != null) {
                    $value = $this->getProductManager()->createEntityValue();
                    $value->setAttribute($attDescription);
                    $value->setLocaleCode('fr');
                    $value->setData('ma description FR '.$ind++);
                    $product->addValue($value);
                    $this->getProductManager()->getStorageManager()->persist($value);
                    $messages[]= "Value 'description' has been translated";
                }
            }
        }

        // get color attribute options
        $attColor = $this->getProductManager()->getAttributeRepository()->findOneByCode('color');
        $colors = array("Red" => "Rouge", "Blue" => "Bleu", "Green" => "Vert");
        // translate
        foreach ($colors as $colorEn => $colorFr) {
            $optValueEn = $this->getProductManager()->getAttributeOptionValueRepository()->findOneBy(array('value' => $colorEn));
            $optValueFr = $this->getProductManager()->getAttributeOptionValueRepository()->findOneBy(array('value' => $colorFr));
            if ($optValueEn and !$optValueFr) {
                $option = $optValueEn->getOption();
                $optValueFr = $this->getProductManager()->createAttributeOptionValue();
                $optValueFr->setValue($colorFr);
                $optValueFr->setLocaleCode('fr');
                $option->addOptionValue($optValueFr);
                $this->getProductManager()->getStorageManager()->persist($optValueFr);
                $messages[]= "Option '".$colorEn."' has been translated";
            }
        }

        $this->getProductManager()->getStorageManager()->flush();

        $this->get('session')->setFlash('notice', implode(', ', $messages));

        return $this->redirect($this->generateUrl('oro_product_product_index'));
    }

}
