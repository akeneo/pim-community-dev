<?php

namespace PimEnterprise\Bundle\SecurityBundle\EventListener;

use Pim\Bundle\EnrichBundle\EnrichEvents;
use PimEnterprise\Bundle\SecurityBundle\Voter\ProductVoter;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\EventDispatcher\GenericEvent;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use Symfony\Component\Security\Core\SecurityContextInterface;

/**
 * Product listener used to handle permissions.
 *
 * @author    Julien Janvier <julien.janvier@akeneo.com>
 * @copyright 2014 Akeneo SAS (http://www.akeneo.com)
 */
class ProductListener implements EventSubscriberInterface
{
    /**
     * @var SecurityContextInterface
     */
    protected $securityContext;

    /**
     * Constructor
     *
     * @param SecurityContextInterface $securityContext
     */
    public function __construct(SecurityContextInterface $securityContext)
    {
        $this->securityContext = $securityContext;
    }


    /**
     * {@inheritdoc}
     */
    public static function getSubscribedEvents()
    {
        return [
            EnrichEvents::PRE_EDIT_PRODUCT => ['checkEditPermission'],
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
        if (false === $this->securityContext->isGranted(ProductVoter::PRODUCT_EDIT, $event->getSubject())) {
            throw new AccessDeniedException();
        }
    }
}
