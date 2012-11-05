<?php
namespace Pim\Bundle\ConnectorIcecatBundle\Load;

use Doctrine\Common\Persistence\ObjectManager;

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
class BatchLoader implements LoadInterface
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

    /**
     * Limit batch size
     * @staticvar
     * @var integer
     */
    protected static $limit = 5000;

    /**
     * Set object manager
     * @param ObjectManager $objectManager
     */
    public function __construct(ObjectManager $objectManager)
    {
        $this->objectManager = $objectManager;
        $this->size = 0;
    }

    /**
     * Persist an object with manager
     * @param $object
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