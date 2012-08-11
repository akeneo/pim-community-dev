<?php

namespace Strixos\CatalogBundle\Controller;

use Symfony\Component\HttpFoundation\Response;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Strixos\CatalogBundle\Document\Product;

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
        // TODO take a look on createQueryBuilder
        $products = $repository->findAll()->limit(1000);
        return array('products' => $products);
    }
}
