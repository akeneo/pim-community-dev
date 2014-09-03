<?php

namespace Pim\Bundle\CatalogBundle\Manager;

use Doctrine\Common\Persistence\ObjectManager;
use Pim\Bundle\CatalogBundle\Entity\Repository\SequentialEditRepository;

/**
 * Sequential edit manager
 *
 * @author    Romain Monceau <romain@akeneo.com>
 * @copyright 2014 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class SequentialEditManager
{
    /** @var ObjectManager */
    protected $om;

    /** @var SequentialEditRepository */
    protected $repository;

    /** @var string */
    protected $entityClass;

    /**
     * Constructor
     *
     * @param ObjectManager $om
     * @param SequentialEditRepository $repository
     * @param string        $entityClass
     */
    public function __construct(ObjectManager $om, SequentialEditRepository $repository, $entityClass)
    {
        $this->om          = $om;
        $this->repository  = $repository;
        $this->entityClass = $entityClass;
    }

    /**
     * Save a sequential edit entity
     */
    public function save()
    {

    }

    /**
     * Returns a sequential edit entity
     *
     * @return \Pim\Bundle\CatalogBundle\Entity\SequentialEdit
     */
    public function createEntity()
    {
        return new $this->entityClass;
    }
}
