<?php
namespace Strixos\CatalogEavBundle\Service;

use Bap\FlexibleEntityBundle\Model\FlexibleEntityManager;
use Doctrine\ORM\EntityManager;

/**
 * Responsible of persist flexible entity, type, fields, values
 *
 * @author     Nicolas Dupont @ Strixos
 * @copyright  Copyright (c) 2012 Strixos SAS (http://www.strixos.com)
 * @license    http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class ProductManager extends FlexibleEntityManager
{
    protected $_em;

    /**
     * Aims to set entity manager
     * @param EntityManager $em
     */
    public function __construct(EntityManager $em)
    {
        $this->_em = $em;
    }

    /**
     * Shortcut to return the entity manager
     *
     * @return EntityManager
     * @throws \LogicException If DoctrineBundle is not available
     */
    public function getObjectManager()
    {
        return $this->_em;
    }


}