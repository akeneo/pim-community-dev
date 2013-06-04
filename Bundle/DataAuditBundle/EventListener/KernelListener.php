<?php

namespace Oro\Bundle\DataAuditBundle\EventListener;

use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpKernel\Event\GetResponseEvent;
use Symfony\Component\HttpKernel\KernelEvents;
use Symfony\Component\Security\Core\SecurityContextInterface;

use Oro\Bundle\DataAuditBundle\Loggable\LoggableManager;

class KernelListener implements EventSubscriberInterface
{
    /**
     * @var SecurityContextInterface
     */
    private $securityContext;

    /**
     * @var LoggableManager
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
