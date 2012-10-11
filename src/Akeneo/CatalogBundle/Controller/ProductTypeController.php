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
 * Product type controller.
 *
 * @author    Nicolas Dupont <nicolas@akeneo.com>
 * @copyright Copyright (c) 2012 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 *
 * @Route("/producttype")
 */
class ProductTypeController extends Controller
{
    /**
     * TODO aims to easily change from one implementation to other
     */
    const DOCTRINE_MANAGER = 'doctrine.orm.entity_manager';
    const DOCTRINE_MONGO_MANAGER = 'doctrine.odm.mongodb.document_manager';
    protected $managerService = self::DOCTRINE_MONGO_MANAGER;
    protected $classShortname = 'AkeneoCatalogBundle:ProductTypeMongo';

    /**
     * Lists all fields
     *
     * @Route("/index")
     * @Template()
     */
    public function indexAction()
    {
        // creates simple grid based on entity or document (ORM or ODM)
        if ($this->managerService == self::DOCTRINE_MONGO_MANAGER) {
            $source = new GridDocument($this->classShortname);
        } else if ($this->managerService == self::DOCTRINE_MANAGER) {
            $source = new GridEntity($this->classShortname);
        } else {
            throw new \Exception('Unknow object manager');
        }
        $grid = $this->get('grid');
        $grid->setSource($source);
        return $grid->getGridResponse('AkeneoCatalogBundle:ProductType:index.html.twig');
    }

}
