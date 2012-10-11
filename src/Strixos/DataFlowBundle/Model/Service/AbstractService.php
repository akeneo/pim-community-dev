<?php
namespace Strixos\DataFlowBundle\Model\Service;

use Doctrine\ORM\EntityManager;

/**
 * aims to define a generic class for ETL services (extract, transform, load)
 *
 * @author    Romain Monceau <romain@akeneo.com>
 * @copyright Copyright (c) 2012 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 * 
 * @abstract
 *
 */
abstract class AbstractService
{
    /**
     * @var EntityManager
     */
    protected $entityManager;
    
    /**
     * aims to inject entity manager
     * @param EntityManager $em
     */
    public function __construct(EntityManager $em)
    {
        $this->entityManager = $em;
        $this->initialize();
    }
    
    /**
     * initialize method could be redefine to prepare environment
     */
    public function initialize()
    {
        // we deal with big download, ensure it will not stopped by max exec
        ini_set('max_execution_time', 0);
    }
    
    /**
     * aims to call each action of the ETL
     * @abstract
     */
    abstract public function process();
}