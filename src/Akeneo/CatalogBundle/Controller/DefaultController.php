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
        $type = new ProductType($manager, $typeCode);
        if (!$type->hasField('sku')) {
            $type->addField('sku', Field::TYPE_TEXT, 'General');
        }
        if (!$type->hasField('name')) {
            $type->addField('name', Field::TYPE_TEXT, 'General');
        }
        if (!$type->hasField('color')) {
            $type->addField('color', Field::TYPE_TEXT, 'Technical');
        }
        // TODO remove and use cascade ?
        $type->persistAndFlush();

        // create product
        $product = $type->newProductInstance();

        $product->setValue('sku', 'mon sku 1');
        $product->setName('mon name 1');
        $product->setColor('Purple');

        echo $product->getColor();

        $product->persistAndFlush();

        echo $product->getColor();

        $name = 'Test';
        return array('name' => $name);
    }
}
