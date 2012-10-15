<?php
namespace Strixos\IcecatConnectorBundle\Load;

/**
 * 
 * Enter description here ...
 *
 * @author    Romain Monceau <romain@akeneo.com>
 * @copyright Copyright (c) 2012 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 * 
 * TODO : Add automatically an import line validated or not
 *
 */
class LanguageLoad extends IcecatLoad
{
    protected $entityManager;
    
    protected $size;
    
    protected static $limit = 2000; //TODO : must be const
    
    public function __construct($entityManager)
    {
        $this->entityManager = $entityManager;
        $this->size = 0;
        $this->list = array();
    }
    
    public function add($entity)
    {
        $this->entityManager->persist($entity);

        if (++$this->size % self::$limit === 0) {
            $this->load();
        }
    }
    
    public function load()
    {
        $this->entityManager->flush();
        $this->entityManager->clear();
        $this->size = 0;
    }
}