<?php
namespace Strixos\IcecatConnectorBundle\Model\Import;

/**
 * Abstract class to import data into local database
 *
 * @author    Romain Monceau @ Akeneo
 * @copyright Copyright (c) 2012 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 * 
 * @abstract
 */
abstract class DataImport
{
    /**
     * Entity Manager
     * @var \Doctrine\ORM\EntityManager
     */
    protected $entityManager;
    
    /**
     * Constructor with entity manager
     * @param \Doctrine\ORM\EntityManager $em
     */
    public function __construct(\Doctrine\ORM\EntityManager $em)
    {
        $this->entityManager = $em;
    }
    
    /**
     * Read file, create entities and save them
     * @abstract
     * @param string $pathFile
     */
    abstract public function process($pathFile);
}