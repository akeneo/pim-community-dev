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
use PimEnterprise\Bundle\SecurityBundle\Attributes;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\EventDispatcher\GenericEvent;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;

/**
 * Product listener used to handle permissions.
 *
 * @author Julien Janvier <julien.janvier@akeneo.com>
 */
class ProductSubscriber implements EventSubscriberInterface
{
    /** @var AuthorizationCheckerInterface */
    protected $authorizationChecker;

    /** @var UserContext */
    protected $userContext;

    /**
     * Constructor
     *
     * @param AuthorizationCheckerInterface $authorizationChecker
     * @param UserContext                   $userContext
     */
    public function __construct(AuthorizationCheckerInterface $authorizationChecker, UserContext $userContext)
    {
        $this->authorizationChecker = $authorizationChecker;
        $this->userContext          = $userContext;
    }

    /**
     * {@inheritdoc}
     */
    public static function getSubscribedEvents()
    {
        return [
            ProductEvents::PRE_EDIT => 'checkEditPermission',
        ];
    }

    /**
     * Throws an access denied exception if the user can not edit the product
     *
     * @param GenericEvent $event
     *
     * @throws AccessDeniedException
     */
    public function checkEditPermission(GenericEvent $event)
    {
        if (false === $this->authorizationChecker->isGranted(Attributes::EDIT, $event->getSubject())) {
            throw new AccessDeniedException();
        }
        $locale = $this->userContext->getCurrentLocale();
        if (false === $this->authorizationChecker->isGranted(Attributes::EDIT_ITEMS, $locale)) {
            throw new AccessDeniedException();
        }
    }
}
