<?php

namespace Strixos\CatalogBundle\Controller;

use Symfony\Component\HttpFoundation\Response;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Strixos\CatalogBundle\Document\Product;

class DefaultController extends Controller
{
    /**
     * @Route("/")
     * @Template()
     */
    public function indexAction()
    {
        $product = new Product();
        $product->setSku('AFooBar');


        $product->addValue('name', 'my product name');

        /*
         * Take a look on http://docs.mongodb.org/manual/use-cases/product-catalog/
         * query = db.Product.find({'values.name': 'my product name'})
         */


        $dm = $this->get('doctrine.odm.mongodb.document_manager');
        $dm->persist($product);
        $dm->flush();
        return new Response('Created product id '.$product->getId());
    }
}
