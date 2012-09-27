<?php

namespace Strixos\CatalogBundle\Controller;

use Doctrine\MongoDB\Connection;

use Strixos\CatalogBundle\Document\MongoProduct;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;

/**
 *
 * @author Nicolas Dupont @ Strixos
 * @copyright  Copyright (c) 2012 Strixos SAS (http://www.strixos.com)
 * @license    http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 *
 */
class DefaultController extends Controller
{

    /**
     * @Route("/default/index")
     * @Template()
     */
    public function indexAction()
    {
/*        $prod = new MongoProduct();
*/

//        $m = new \Mongo();
//        $db = $m->selectDB('exemple');
//        $collection = $m->createCollection('test')


        // TODO: not use ODM ! only Mongo wrapper

        $dbName = 'strixos';
        $collectionName = 'flexiblemongoprod';
        $con = new Connection();
        $collection = $con->selectCollection($dbName, $collectionName);

        $myProd = array(
            'sku'      => '12344',
            'features' =>array(
                'size'  => 12,
                'color' => 'red'

            )
        );
        $collection->insert($myProd);

        die('pouet');
    }
}
