<?php

namespace Oro\Bundle\SecurityBundle\EventListener;

use Doctrine\Common\Util\ClassUtils;
use Symfony\Component\HttpKernel\Event\FilterControllerEvent;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Psr\Log\LoggerInterface;
use Oro\Bundle\SecurityBundle\SecurityFacade;

class ControllerListener
{
    /**
     * @var SecurityFacade
     */
    private $securityFacade;

    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * Constructor
     *
     * @param SecurityFacade $securityFacade
     * @param LoggerInterface $logger
     */
    public function __construct(
        SecurityFacade $securityFacade,
        LoggerInterface $logger
    ) {
        $this->securityFacade = $securityFacade;
        $this->logger = $logger;
    }

    public function onKernelController(FilterControllerEvent $event)
    {
        $controller = $event->getController();
        /*
         * $controller passed can be either a class or a Closure. This is not usual in Symfony2 but it may happen.
         * If it is a class, it comes in array format
         */
        if (is_array($controller)) {
            list($object, $method) = $controller;
            $className = ClassUtils::getClass($object);

            $this->logger->info(
                sprintf('User invoked class: "%s", Method: "%s".', $className, $method)
            );

            if (!$this->securityFacade->isClassMethodGranted($className, $method)) {
                // check if we have internal action - show blank
                if ($event->getRequest() !== null && $event->getRequest()->attributes->get('_route') == null) {
                    return new Response('');
                }
                throw new AccessDeniedException('Access denied.');
            }
        }
    }
}
