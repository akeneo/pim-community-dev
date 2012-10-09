<?php
namespace Strixos\IcecatConnectorBundle\Model\Load;

/**
 * Abstract class to load data from files
 *
 * @author    Romain Monceau @ Akeneo
 * @copyright Copyright (c) 2012 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 * 
 * @abstract
 */
abstract class DataLoader
{
    /**
     * Entity manager
     * @var \Doctrine\ORM\EntityManager $em
     */
    protected $_entityManager;
    
    /**
     * Constructor
     * @param \Doctrine\ORM\EntityManager $em
     */
    public function __constructor(\Doctrine\ORM\EntityManager $em)
    {
        $this->_entityManager = $em;
    }
    
    /**
     * Read file, create entities and save them
     * @abstract
     * @param string $pathFile
     */
    abstract public function process($pathFile);
}