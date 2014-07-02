<?php

namespace Pim\Bundle\CatalogBundle\Manager;

use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Doctrine\Common\Persistence\ObjectManager;
use Pim\Bundle\CatalogBundle\CatalogEvents;
use Pim\Bundle\CatalogBundle\Entity\Family;
use Pim\Bundle\CatalogBundle\Entity\Repository\FamilyRepository;
use Pim\Bundle\UserBundle\Context\UserContext;

/**
 * Family manager
 *
 * @author    Nicolas Dupont <nicolas@akeneo.com>
 * @copyright 2014 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class FamilyManager
{
    /** @var FamilyRepository */
    protected $repository;

    /** @var UserContext */
    protected $userContext;

    /** @var ObjectManager */
    protected $objectManager;

    /** @var EventDispatcherInterface */
    protected $eventDispatcher;

    /**
     * Constructor
     *
     * @param FamilyRepository         $repository
     * @param UserContext              $userContext
     * @param ObjectManager            $objectManager
     * @param EventDispatcherInterface $eventDispatcher
     */
    public function __construct(
        FamilyRepository $repository,
        UserContext $userContext,
        ObjectManager $objectManager,
        EventDispatcherInterface $eventDispatcher
    ) {
        $this->repository      = $repository;
        $this->userContext     = $userContext;
        $this->objectManager   = $objectManager;
        $this->eventDispatcher = $eventDispatcher;
    }

    /**
     * Get choices
     *
     * @return array
     */
    public function getChoices()
    {
        return $this->repository->getChoices(
            ['localeCode' => $this->userContext->getCurrentLocaleCode()]
        );
    }

    /**
     * Remove a family
     *
     * @param Family $family
     */
    public function remove(Family $family)
    {
        $this->eventDispatcher->dispatch(CatalogEvents::PRE_REMOVE_FAMILY, new GenericEvent($family));

        $this->objectManager->remove($family);
        $this->objectManager->flush();
    }
}
