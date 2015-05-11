<?php

namespace Pim\Bundle\VersioningBundle\EventSubscriber;

use Pim\Bundle\VersioningBundle\Event\BuildVersionEvent;
use Pim\Bundle\VersioningBundle\Event\BuildVersionEvents;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Security\Core\SecurityContextInterface;

/**
 * Add current user
 *
 * @author    Nicolas Dupont <nicolas@akeneo.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class AddUserSubscriber implements EventSubscriberInterface
{
    /** @var SecurityContextInterface */
    protected $securityContext;

    /**
     * @param SecurityContextInterface $securityContext
     */
    public function __construct(SecurityContextInterface $securityContext = null)
    {
        $this->securityContext = $securityContext;
    }

    /**
     * {@inheritdoc}
     */
    public static function getSubscribedEvents()
    {
        return [
            BuildVersionEvents::PRE_BUILD => 'preBuild',
        ];
    }

    /**
     * @param BuildVersionEvent $event
     *
     * @return BuildVersionEvent
     */
    public function preBuild(BuildVersionEvent $event)
    {
        if (null === $this->securityContext) {
            return $event;
        }

        $token = $this->securityContext->getToken();
        if (null !== $token && $this->securityContext->isGranted('IS_AUTHENTICATED_REMEMBERED')) {
            $event->setUsername($token->getUser()->getUsername());
        }

        return $event;
    }
}
