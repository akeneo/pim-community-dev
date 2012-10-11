<?php

namespace Akeneo\CatalogBundle\Controller;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Akeneo\CatalogBundle\Entity\ProductField;
use Akeneo\CatalogBundle\Document\ProductFieldMongo;
use Akeneo\CatalogBundle\Form\ProductFieldType;
use APY\DataGridBundle\Grid\Source\Entity as GridEntity;
use APY\DataGridBundle\Grid\Source\Document as GridDocument;
use APY\DataGridBundle\Grid\Action\RowAction;

/**
 * Product controller.
 *
 * @author    Nicolas Dupont <nicolas@akeneo.com>
 * @copyright Copyright (c) 2012 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 *
 * @Route("/product")
 */
class ProductController extends Controller
{
    /**
     * TODO aims to easily change from one implementation to other
     */
    const DOCTRINE_MANAGER = 'doctrine.orm.entity_manager';
    const DOCTRINE_MONGO_MANAGER = 'doctrine.odm.mongodb.document_manager';
    protected $managerService = self::DOCTRINE_MONGO_MANAGER;
    protected $classShortname = 'AkeneoCatalogBundle:ProductMongo';

    /**
     * Lists all fields
     *
     * @Route("/index")
     * @Template()
     */
    public function indexAction()
    {
        $manager = $this->get($this->managerService);
        $products = $manager->getRepository($this->classShortname)->findAll()->limit(1000);
        return array('products' => $products);
    }

}
