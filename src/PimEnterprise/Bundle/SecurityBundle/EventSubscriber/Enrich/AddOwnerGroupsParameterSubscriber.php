<?php

namespace PimEnterprise\Bundle\SecurityBundle\EventSubscriber\Enrich;

use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\EventDispatcher\GenericEvent;
use Oro\Bundle\UserBundle\Entity\User;
use Pim\Bundle\EnrichBundle\Event\ProductEvents;
use PimEnterprise\Bundle\SecurityBundle\Entity\Repository\CategoryAccessRepository;
use Pim\Bundle\UserBundle\Context\UserContext;
use PimEnterprise\Bundle\SecurityBundle\Attributes;

/**
 * Add the owner groups parameter to the product edit template parameters
 *
 * @author    Nicolas Dupont <nicolas@akeneo.com>
 * @copyright 2014 Akeneo SAS (http://www.akeneo.com)
 */
class AddOwnerGroupsParameterSubscriber implements EventSubscriberInterface
{
    /** @var CategoryAccessRepository */
    protected $repository;

    /** @var UserContext */
    protected $userContext;

    /**
     * @param CategoryAccessRepository $repository  the owner repo
     * @param UserContext              $userContext the user context
     */
    public function __construct(CategoryAccessRepository $repository, UserContext $userContext)
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
            ProductEvents::PRE_RENDER_EDIT => 'addOwnerGroupsParameter',
        ];
    }

    /**
     * Add a proposition form view parameter to the template parameters
     *
     * @param GenericEvent $event
     */
    public function addOwnerGroupsParameter(GenericEvent $event)
    {
        try {
            $parameters = $event->getArgument('parameters');
            if (!array_key_exists('product', $parameters)) {
                throw new \InvalidArgumentException();
            }
        } catch (\InvalidArgumentException $e) {
            return;
        }

        $parameters['ownerGroups'] = $this->repository->getGrantedUserGroupsForProduct(
            $parameters['product'],
            Attributes::OWN_PRODUCTS
        );

        $event->setArgument('parameters', $parameters);
    }
}
