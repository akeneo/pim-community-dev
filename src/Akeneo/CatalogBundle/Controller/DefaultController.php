<?php

namespace Akeneo\CatalogBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Akeneo\CatalogBundle\Model\Product;
use Akeneo\CatalogBundle\Model\ProductType;
use Akeneo\CatalogBundle\Entity\Field;

class DefaultController extends Controller
{
    /**
     * @Route("/")
     * @Template()
     */
    public function indexAction()
    {
        $manager = $this->getDoctrine()->getEntityManager();

        $ind = time();

        // create type
        $typeCode = 'tshirt'.$ind;
        //$type = new ProductType($manager);
        $type = $this->container->get('akeneo.catalog.model_producttype');
        $type = $type->create($typeCode);
        if (!$type->getField('sku')) {
            $type->addField('sku', Field::TYPE_TEXT, 'General');
        }
        if (!$type->getField('name')) {
            $type->addField('name', Field::TYPE_TEXT, 'General');
        }
        if (!$type->getField('color')) {
            $type->addField('color', Field::TYPE_TEXT, 'Technical');
        }

        $type->persist();
        $type->flush();

        // create product
        $product = $type->newProductInstance();

        $product->setValue('sku', 'mon sku 1');
        $product->setName('mon name 1');
        $product->setColor('Green');

        $product->persist();
        $product->flush();
        echo $product->getColor();

        $product->setValue('color', 'Vert', 'fr_fr');
        $product->persist();
        $product->flush();
        echo $product->getColor();

        $name = 'Test';
        return array('name' => $name);
    }
}
