<?php

namespace Pim\Bundle\CatalogBundle\Manager;

use Doctrine\Common\Persistence\ObjectManager;
use Pim\Bundle\CatalogBundle\Model\AssociationTypeInterface;
use Pim\Bundle\CatalogBundle\Repository\AssociationTypeRepositoryInterface;

/**
 * Association type manager
 *
 * @author    Romain Monceau <romain@akeneo.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class AssociationTypeManager
{
    /** @var AssociationTypeRepositoryInterface $repository */
    protected $repository;

    /** @var ObjectManager */
    protected $objectManager;

    /**
     * Constructor
     *
     * @param AssociationTypeRepositoryInterface $repository
     * @param ObjectManager                      $objectManager
     */
    public function __construct(
        AssociationTypeRepositoryInterface $repository,
        ObjectManager $objectManager
    ) {
        $this->repository      = $repository;
        $this->objectManager   = $objectManager;
    }

    /**
     * Get association types
     *
     * @return array
     */
    public function getAssociationTypes()
    {
        return $this->repository->findAll();
    }

    /**
     * Remove an association type
     *
     * @param AssociationTypeInterface $associationType
     *
     * @deprecated will be removed in 1.4, replaced by AssociationTypeRemover::remove
     */
    public function remove(AssociationTypeInterface $associationType)
    {
        $this->objectManager->remove($associationType);
        $this->objectManager->flush();
    }
}
