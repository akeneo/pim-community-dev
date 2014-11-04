<?php

namespace Pim\Bundle\CatalogBundle\Manager;

use Doctrine\Common\Persistence\ObjectManager;
use Pim\Bundle\CatalogBundle\Entity\Repository\FamilyRepository;
use Pim\Bundle\CatalogBundle\Event\FamilyEvents;
use Pim\Bundle\CatalogBundle\Model\FamilyInterface;
use Pim\Bundle\UserBundle\Context\UserContext;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\EventDispatcher\GenericEvent;

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
     * @param FamilyInterface $family
     */
    public function remove(FamilyInterface $family)
    {
        $this->eventDispatcher->dispatch(FamilyEvents::PRE_REMOVE, new GenericEvent($family));

        $this->objectManager->remove($family);
        $this->objectManager->flush();
    }
}
