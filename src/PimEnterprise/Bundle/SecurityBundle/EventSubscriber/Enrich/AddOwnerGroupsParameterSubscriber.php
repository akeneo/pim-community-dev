<?php

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2014 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace PimEnterprise\Bundle\SecurityBundle\EventSubscriber\Enrich;

use Pim\Bundle\EnrichBundle\Event\ProductEvents;
use Pim\Bundle\UserBundle\Context\UserContext;
use Pim\Bundle\UserBundle\Entity\User;
use PimEnterprise\Bundle\SecurityBundle\Attributes;
use PimEnterprise\Bundle\SecurityBundle\Entity\Repository\CategoryAccessRepository;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\EventDispatcher\GenericEvent;

/**
 * Add the owner groups parameter to the product edit template parameters
 *
 * @author Nicolas Dupont <nicolas@akeneo.com>
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
     * Add a product draft form view parameter to the template parameters
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
