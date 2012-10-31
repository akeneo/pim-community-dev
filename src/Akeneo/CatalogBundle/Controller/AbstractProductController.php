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
use APY\DataGridBundle\Grid\Column\TextColumn;

/**
 * Controller independent of real product storage
 *
 * @author    Nicolas Dupont <nicolas@akeneo.com>
 * @copyright Copyright (c) 2012 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 *
 */
abstract class AbstractProductController extends Controller
{
    /**
     * TODO aims to easily change from one implementation to other
     */
    const DOCTRINE_MANAGER = 'doctrine.orm.entity_manager';
    const DOCTRINE_MONGO_MANAGER = 'doctrine.odm.mongodb.document_manager';

    /**
     * Get used object manager
     */
    public function getObjectManagerService()
    {
        return $this->container->getParameter('pim.catalog.product.objectmanager');
    }

    /**
     * Get object class used by controller
     */
    public abstract function getObjectShortName();


    /**
     * Return grid source for APY grid
     * @return APY\DataGridBundle\Grid\Source\Entity
     */
    public function getGridSource()
    {
        // source to create simple grid based on entity or document (ORM or ODM)
        if ($this->getObjectManagerService() == self::DOCTRINE_MONGO_MANAGER) {
            return new GridDocument($this->getObjectShortName());
        } else if ($this->getObjectManagerService() == self::DOCTRINE_MANAGER) {
            return new GridEntity($this->getObjectShortName());
        } else {
            throw new \Exception('Unknow object manager');
        }
    }

    /**
     * Return full name of object class
     * @return unknown
     */
    public function getObjectClassFullName()
    {
        $om = $this->container->get($this->getObjectManagerService());
        $metadata = $om->getClassMetadata($this->getObjectShortName());
        $classFullName = $metadata->getName();
        return $classFullName;
    }

    /**
     * Return new instance of object
     * @return unknown
     */
    public function getNewObject()
    {
        $classFullName = $this->getObjectClassFullName();
        $entity = new $classFullName();
        return $entity;
    }
}
