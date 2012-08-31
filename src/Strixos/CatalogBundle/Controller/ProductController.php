<?php

namespace Strixos\CatalogBundle\Controller;

use Symfony\Component\HttpFoundation\Response;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Strixos\CatalogBundle\Document\Product;
use Strixos\CatalogBundle\Form\Type\ProductType;

/**
 *
 * @author Nicolas Dupont @ Strixos
 * @copyright  Copyright (c) 2012 Strixos SAS (http://www.strixos.com)
 * @license    http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 *
 */
class ProductController extends Controller
{
    /**
     * @Route("/product/index")
     * @Template()
     */
    public function indexAction()
    {
        $manager = $this->get('doctrine.odm.mongodb.document_manager');
        $repository = $manager->getRepository('StrixosCatalogBundle:Product');
        $products = $repository->findAll()->limit(1000);
        return array('products' => $products);
    }

    /**
    * @Route("/product/edit/{id}")
    * @Template()
    */
    public function editAction($id)
    {
        // get product
        $manager = $this->get('doctrine.odm.mongodb.document_manager');
        $repository = $manager->getRepository('StrixosCatalogBundle:Product');
        $product = $repository->find($id);
        if (!$product) {
            throw $this->createNotFoundException('No product found for id '.$id);
        }
        // get attribute set
        $em = $this->getDoctrine()->getEntityManager();
        $set = $em->getRepository('StrixosCatalogBundle:Set')->findOneBy(array('code' => $product->getAttributeSetCode()));
        if (!$set) {
            throw $this->createNotFoundException('No set found for code '.$product->getAttributeSetCode());
        }
        // set list of available attribute to prepare drag n drop list
        $productType = new ProductType();
        $productType->setAttributeSet($set);
        // prepare form
        $form = $this->createForm($productType, $product);
        // render form
        return $this->render(
            'StrixosCatalogBundle:Product:edit.html.twig', array('form' => $form->createView(),)
        );
    }

    /**
    * @Route("/product/save")
    * @Template()
    */
    public function saveAction(Request $request)
    {
        // TODO
    }

    /**
    * @Route("/product/search")
    * @Template()
    */
    public function searchAction()
    {
        $manager = $this->get('doctrine.odm.mongodb.document_manager');
        $searches = array();

        // all products
        $start = microtime(true);
        $queryBuilder = $manager->createQueryBuilder('StrixosCatalogBundle:Product');
        $products = $queryBuilder->getQuery()->execute();
        // TODO use symfony profiler to get time and query to string ?
        $end = microtime(true);
        $memUsed = memory_get_usage(true);
        $search = new \stdClass;
        $search->description = 'Find all products';
        $search->time = (round($end - $start, 2)) . ' seconds';
        $search->memory = round(($memUsed / 1024)/1024, 0).' Mo';
        $search->results = count($products);
        $searches[]= $search;

        // all products of type laptop
        $start = microtime(true);
        $queryBuilder = $manager->createQueryBuilder('StrixosCatalogBundle:Product')
            ->field('attributeSetCode')->equals('laptop');
        $products = $queryBuilder->getQuery()->execute();
        // TODO use symfony profiler to get time and query to string ?
        $end = microtime(true);
        $memUsed = memory_get_usage(true);
        $search = new \stdClass;
        $search->description = 'Find all laptops (product type)';
        $search->time = (round($end - $start, 2)) . ' seconds';
        $search->memory = round(($memUsed / 1024)/1024, 0).' Mo';
        $search->results = count($products);
        $searches[]= $search;

        // all products where name 'My t-shirt 15'
        $start = microtime(true);
        $queryBuilder = $manager->createQueryBuilder('StrixosCatalogBundle:Product')
        ->field('values.name')->equals('My tshirt 15');
        $products = $queryBuilder->getQuery()->execute();
        // TODO use symfony profiler to get time and query to string ?
        $end = microtime(true);
        $memUsed = memory_get_usage(true);
        $search = new \stdClass;
        $search->description = 'Find all product where name is My t-shirt 15';
        $search->time = (round($end - $start, 2)) . ' seconds';
        $search->memory = round(($memUsed / 1024)/1024, 0).' Mo';
        $search->results = count($products);
        $searches[]= $search;

        // all products where name 'My t-shirt 1%'
        $start = microtime(true);
        $regexObj = new \MongoRegex("/^My tshirt*/i");
        $queryBuilder = $manager->createQueryBuilder('StrixosCatalogBundle:Product')
            ->field('values.name')->equals($regexObj);
        $products = $queryBuilder->getQuery()->execute();
        // TODO use symfony profiler to get time and query to string ?
        $end = microtime(true);
        $memUsed = memory_get_usage(true);
        $search = new \stdClass;
        $search->description = 'Find all product where name is My t-shirt%';
        $search->time = (round($end - $start, 2)) . ' seconds';
        $search->memory = round(($memUsed / 1024)/1024, 0).' Mo';
        $search->results = count($products);
        $searches[]= $search;

        // all products where laptop_hdd 'Sata%'
        $start = microtime(true);
        $regexObj = new \MongoRegex("/^Sata*/i");
        $queryBuilder = $manager->createQueryBuilder('StrixosCatalogBundle:Product')
        ->field('values.laptop_hdd')->equals($regexObj);
        $products = $queryBuilder->getQuery()->execute();
        // TODO use symfony profiler to get time and query to string ?
        $end = microtime(true);
        $memUsed = memory_get_usage(true);
        $search = new \stdClass;
        $search->description = 'Find all product where laptop_hdd is Sata%';
        $search->time = (round($end - $start, 2)) . ' seconds';
        $search->memory = round(($memUsed / 1024)/1024, 0).' Mo';
        $search->results = count($products);
        $searches[]= $search;

        // all products where laptop_hdd 'Sata 200 GO%'
        $start = microtime(true);
        $queryBuilder = $manager->createQueryBuilder('StrixosCatalogBundle:Product')
            ->field('values.laptop_hdd')->equals('Sata 200 GO');
        $products = $queryBuilder->getQuery()->execute();
        // TODO use symfony profiler to get time and query to string ?
        $end = microtime(true);
        $memUsed = memory_get_usage(true);
        $search = new \stdClass;
        $search->description = 'Find all product where laptop_hdd is Sata 200 GO';
        $search->time = (round($end - $start, 2)) . ' seconds';
        $search->memory = round(($memUsed / 1024)/1024, 0).' Mo';
        $search->results = count($products);
        $searches[]= $search;

        // all products where laptop_hdd 'Sata 200 GO%'
        $start = microtime(true);
        $queryBuilder = $manager->createQueryBuilder('StrixosCatalogBundle:Product')
            ->field('values.laptop_hdd')->equals('Sata 200 GO')
            ->field('values.laptop_cpu')->equals('I7');
        $products = $queryBuilder->getQuery()->execute();
        // TODO use symfony profiler to get time and query to string ?
        $end = microtime(true);
        $memUsed = memory_get_usage(true);
        $search = new \stdClass;
        $search->description = 'Find all product where laptop_hdd is Sata 200 GO and laptop_cpu is I7';
        $search->time = (round($end - $start, 2)) . ' seconds';
        $search->memory = round(($memUsed / 1024)/1024, 0).' Mo';
        $search->results = count($products);
        $searches[]= $search;

        return array('searches' => $searches);
    }
}
