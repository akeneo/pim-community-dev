<?php

namespace Oro\Bundle\DataAuditBundle\EventListener;

use Oro\Bundle\DataAuditBundle\Loggable\LoggableManager;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpKernel\Event\GetResponseEvent;
use Symfony\Component\HttpKernel\KernelEvents;
use Symfony\Component\Security\Core\SecurityContextInterface;

class KernelListener implements EventSubscriberInterface
{
    /**
     * @var \Symfony\Component\Security\Core\SecurityContextInterface
     */
    private $securityContext;

    /**
     * @var \Oro\Bundle\DataAuditBundle\Loggable\LoggableManager
     */
    private $loggableManager;

    /**
     * @param LoggableManager          $loggableManager
     * @param SecurityContextInterface $securityContext
     */
    public function __construct(LoggableManager $loggableManager, SecurityContextInterface $securityContext = null)
    {
        $this->loggableManager = $loggableManager;
        $this->securityContext = $securityContext;
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
            $this->loggableManager->setUsername($token);
        }
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
}
