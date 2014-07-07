<?php

namespace PimEnterprise\Bundle\SecurityBundle\EventListener;

use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\EventDispatcher\GenericEvent;
use Oro\Bundle\UserBundle\Entity\User;
use Pim\Bundle\EnrichBundle\EnrichEvents;
use PimEnterprise\Bundle\SecurityBundle\Entity\Repository\CategoryOwnershipRepository;
use Pim\Bundle\UserBundle\Context\UserContext;

/**
 * Add the owner roles parameter to the product edit template parameters
 *
 * @author    Nicolas Dupont <nicolas@akeneo.com>
 * @copyright 2014 Akeneo SAS (http://www.akeneo.com)
 */
class AddOwnerRolesParameterListener implements EventSubscriberInterface
{
    /** @var CategoryOwnershipRepository */
    protected $repository;

    /** @var UserContext */
    protected $userContext;

    /**
     * @param CategoryOwnershipRepository $repository  the owner repo
     * @param UserContext                 $userContext the user context
     */
    public function __construct(CategoryOwnershipRepository $repository, UserContext $userContext)
    {
        $this->repository  = $repository;
        $this->userContext = $userContext;
    }

    /**
     * {@inheritdoc}
     */
    public static function getSubscribedEvents()
    {
        return [
            EnrichEvents::PRE_RENDER_PRODUCT_EDIT => 'addOwnerRolesParameter',
        ];
    }

    /**
     * Add a proposition form view parameter to the template parameters
     *
     * @param GenericEvent $event
     */
    public function addOwnerRolesParameter(GenericEvent $event)
    {
        try {
            $parameters = $event->getArgument('parameters');
            if (!array_key_exists('product', $parameters)) {
                throw new \InvalidArgumentException();
            }
        } catch (\InvalidArgumentException $e) {
            return;
        }

        $parameters['ownerRoles'] = $this->repository->findRolesForProduct($parameters['product']);

        $event->setArgument('parameters', $parameters);
    }
}
