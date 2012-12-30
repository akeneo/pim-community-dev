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
     * @Route("/index")
     * @Template()
     *
     * @return multitype
     */
    public function indexAction()
    {
        $products = $this->getProductManager()->getEntityRepository()->findByWithAttributes();

        return array('products' => $products);
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

        return array('products' => $products);
    }

    /**
     * @Route("/queryonlynameandsku")
     * @Template("OroProductBundle:Product:index.html.twig")
     *
     * @return multitype
     */
    public function queryonlynameandskuAction()
    {
        // get all entity fields and directly get attributes values
        $products = $this->getProductManager()->getEntityRepository()->findByWithAttributes(array('sku', 'name'));

        return array('products' => $products);
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
        $products = $this->getProductManager()->getEntityRepository()->findByWithAttributes(array(), array('sku' => 'sku-1'));

        return array('products' => $products);
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
        $products = $this->getProductManager()->getEntityRepository()->findByWithAttributes(array('size'), array('size' => 175));

        return array('products' => $products);
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
            array('size' => 175, 'description' => 'my long description 3')
        );

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

        // force in english
        $this->getProductManager()->setLocaleCode('en');

        // get attributes
        $attName = $this->getProductManager()->getAttributeRepository()->findOneByCode('name');
        $attDescription = $this->getProductManager()->getAttributeRepository()->findOneByCode('description');
        $attSize = $this->getProductManager()->getAttributeRepository()->findOneByCode('size');
        $attColor = $this->getProductManager()->getAttributeRepository()->findOneByCode('color');
        // get first attribute option
        $optColor = $this->getProductManager()->getAttributeOptionRepository()->findOneBy(array('attribute' => $attColor));

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

            // add product with sku, name, description, color and size
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
                if ($attSize) {
                    $valueSize = $this->getProductManager()->getNewAttributeValueInstance();
                    $valueSize->setAttribute($attSize);
                    $valueSize->setData(175);
                    $newProduct->addValue($valueSize);
                }
                if ($attColor) {
                    $value = $this->getProductManager()->getNewAttributeValueInstance();
                    $value->setAttribute($attColor);
                    $value->setData($optColor); // we set option as data, you can use $value->setOption($optColor) too
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
                    $value = $this->getProductManager()->getNewAttributeValueInstance();
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
                    $value = $this->getProductManager()->getNewAttributeValueInstance();
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
                $optValueFr = $this->getProductManager()->getNewAttributeOptionValueInstance();
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
