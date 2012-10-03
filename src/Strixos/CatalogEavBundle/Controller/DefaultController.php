<?php

namespace Strixos\CatalogEavBundle\Controller;

use Strixos\CatalogEavBundle\Entity\Group;
use Strixos\CatalogEavBundle\Entity\Type;
use Strixos\CatalogEavBundle\Entity\Value;
use Strixos\CatalogEavBundle\Entity\Field;
use Strixos\CatalogEavBundle\Entity\Product;


use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;

class DefaultController extends Controller
{
    /**
     * @Route("/")
     * @Template()
     */
    public function indexAction()
    {

        $factory = $this->container->get('strixos_catalog_eav.productmanager');
        $manager = $factory->getObjectManager();

        $fieldCode = 'name';
        if (!$fieldName = $manager->getRepository('StrixosCatalogEavBundle:Field')
                ->findOneBy(array('code' => $fieldCode)))
        {
            $fieldName = new Field();
            $fieldName->setCode($fieldCode);
            $manager->persist($fieldName);
        }
        
        $fieldCode = 'sku';
        if (!$fieldSku = $manager->getRepository('StrixosCatalogEavBundle:Field')
                ->findOneBy(array('code' => $fieldCode)))
        {
            $fieldSku = new Field();
            $fieldSku->setCode($fieldCode);
            $manager->persist($fieldSku);
        }


        $typCode = 'T-shirt';
        if (!$type = $manager->getRepository('StrixosCatalogEavBundle:Type')
                ->findOneBy(array('code' => $typCode))) {
            $type = new Type();
            $type->setCode($typCode);
            $type->addField($fieldName);
            $type->addField($fieldSku);
            $manager->persist($type);
        }

        $groupCode = 'Informations';
        if (!$group = $manager->getRepository('StrixosCatalogEavBundle:Group')
                ->findOneBy(array('code' => $groupCode)))
        {
            $group = new Group();
            $group->setCode($groupCode);
            $group->setType($type);
            $group->addField($fieldName);
            $group->addField($fieldSku);
            $manager->persist($group);
        }

        $product = new Product();
        $product->setType($type);
        $manager->persist($product);
        
        echo $product->getType()->getCode();

        /*if (!$value = $manager->getRepository('StrixosCatalogEavBundle:Value')
                ->findOneByField($fieldName))
        {
            $value = new Value();
            $value->setField($fieldName);
            $value->setProduct($product);
            $value->setContent('my product name');
            $manager->persist($value);
        }
        
        if (!$value = $manager->getRepository('StrixosCatalogEavBundle:Value')
        		->findOneByField($fieldSku))
        {
            $value = new Value();
            $value->setField($fieldSku);
            $value->setProduct($product);
            $value->setContent('my-product-sku');
            $manager->persist($value);
        }*/

        $manager->flush();
        
        
        //$product = new Product();
        $product = $manager->getRepository('StrixosCatalogEavBundle:Product')
            ->findOneById(1);
        
        $product->setSku('yellow');
    
        echo 'POUIC '. $product->getSku();
        
        echo '<hr />';

        $name = 'pouet';
        return array('name' => $name, 'product' => $product);
    }
}
