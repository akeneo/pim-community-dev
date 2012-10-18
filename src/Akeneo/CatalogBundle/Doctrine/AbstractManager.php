<?php
namespace Akeneo\CatalogBundle\Doctrine;
use Doctrine\Common\Persistence\ObjectManager;

/**
 * Abstract manager
 *
 * @author    Nicolas Dupont <nicolas@akeneo.com>
 * @copyright Copyright (c) 2012 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 *
 */
abstract class AbstractManager
{
    protected $objectManager;
    protected $repository;
    protected $class;

    /**
     * Constructor.
     *
     * @param ObjectManager           $om
     * @param string                  $class
     */
    public function __construct(ObjectManager $om, $class)
    {
        $this->objectManager = $om;
        $this->repository = $om->getRepository($class);
        $metadata = $om->getClassMetadata($class);
        $this->class = $metadata->getName();
    }

    /**
     * Get object manager
     * @return ObjectManager
     */
    public function getManager()
    {
        return $this->objectManager;
    }

    /**
    * Get class
    * @return
    */
    public function getClass()
    {
        return $this->class;
    }

}