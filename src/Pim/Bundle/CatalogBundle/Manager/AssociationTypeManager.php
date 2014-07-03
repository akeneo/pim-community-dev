<?php

namespace Pim\Bundle\CatalogBundle\Manager;

use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\EventDispatcher\GenericEvent;
use Doctrine\Common\Persistence\ObjectManager;
use Pim\Bundle\CatalogBundle\CatalogEvents;
use Pim\Bundle\CatalogBundle\Entity\AssociationType;
use Pim\Bundle\CatalogBundle\Entity\Repository\AssociationTypeRepository;

/**
 * Association type manager
 *
 * @author    Romain Monceau <romain@akeneo.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class AssociationTypeManager
{
    /** @var AssociationTypeRepository $repository */
    protected $repository;

    /** @var ObjectManager */
    protected $objectManager;

    /** @var EventDispatcherInterface */
    protected $eventDispatcher;

    /**
     * Constructor
     *
     * @param AssociationTypeRepository $repository
     * @param ObjectManager             $objectManager
     * @param EventDispatcherInterface  $eventDispatcher
     */
    public function __construct(
        AssociationTypeRepository $repository,
        ObjectManager $objectManager,
        EventDispatcherInterface $eventDispatcher
    ) {
        $this->repository      = $repository;
        $this->objectManager   = $objectManager;
        $this->eventDispatcher = $eventDispatcher;
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
     * @param AssociationType $associationType
     */
    public function remove(AssociationType $associationType)
    {
        $this->eventDispatcher->dispatch(
            CatalogEvents::PRE_REMOVE_ASSOCIATION_TYPE,
            new GenericEvent($associationType)
        );

        $this->objectManager->remove($associationType);
        $this->objectManager->flush();
    }
}
