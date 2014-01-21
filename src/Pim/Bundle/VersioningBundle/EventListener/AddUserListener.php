<?php

namespace Pim\Bundle\VersioningBundle\EventListener;

use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpKernel\Event\GetResponseEvent;
use Symfony\Component\HttpKernel\KernelEvents;
use Symfony\Component\Security\Core\SecurityContextInterface;

/**
 * Add current user to version builder
 *
 * @author    Nicolas Dupont <nicolas@akeneo.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class AddUserListener implements EventSubscriberInterface
{
    /**
     * @var SecurityContextInterface
     */
    protected $securityContext;

    /**
     * Version listener
     * @var AddVersionListener
     */
    protected $listener;

    /**
     * @param AddVersionListener       $listener
     * @param SecurityContextInterface $securityContext
     */
    public function __construct(AddVersionListener $listener, SecurityContextInterface $securityContext = null)
    {
        $this->listener        = $listener;
        $this->securityContext = $securityContext;
    }

    /**
     * {@inheritdoc}
     */
    public static function getSubscribedEvents()
    {
        return array(
            KernelEvents::REQUEST => 'onKernelRequest',
        );
    }

    /**
     * @param GetResponseEvent $event
     */
    public function onKernelRequest(GetResponseEvent $event)
    {
        if (null === $this->securityContext) {
            return;
        }

        $token = $this->securityContext->getToken();
        if (null !== $token && $this->securityContext->isGranted('IS_AUTHENTICATED_REMEMBERED')) {
            $this->listener->setUsername($token);
        }
    }
}
