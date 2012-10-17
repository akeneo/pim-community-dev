<?php
namespace Strixos\IcecatConnectorBundle\Load;

/**
 * Provide default batch loader
 *
 * @author    Romain Monceau <romain@akeneo.com>
 * @copyright Copyright (c) 2012 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 *
 * TODO : Add automatically an import line validated or not
 *
 */
class BatchLoader extends AbstractLoader
{
    /**
     * Object manager which deals with persist entity or document
     * @var ObjectManager
     */
    protected $objectManager;

    /**
     * Batch size
     * @var integer
     */
    protected $size;

    protected static $limit = 5000; //TODO : should be configurable

    /**
     * Set object manager
     * @param ObjectManager $objectManager
     */
    public function __construct($objectManager)
    {
        $this->objectManager = $objectManager;
        $this->size = 0;
    }

    /**
     * Persist an object with manager
     * @param Object $object
     */
    public function add($object)
    {
        $this->objectManager->persist($object);
        if (++$this->size % self::$limit === 0) {
            $this->load();
        }
    }

    /**
     * Flush persisted entity
     */
    public function load()
    {
        $this->objectManager->flush();
        $this->objectManager->clear();
    }
}