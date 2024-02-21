<?php

namespace Oro\Bundle\SecurityBundle\EventListener;

use Doctrine\Common\Util\ClassUtils;
use Oro\Bundle\SecurityBundle\Exception\AccessDeniedException;
use Oro\Bundle\SecurityBundle\SecurityFacade;
use Psr\Log\LoggerInterface;
use Symfony\Component\HttpKernel\Event\ControllerEvent;
use Symfony\Component\HttpKernel\HttpKernelInterface;

class ControllerListener
{
    private SecurityFacade $securityFacade;
    private LoggerInterface $logger;

    public function __construct(
        SecurityFacade $securityFacade,
        LoggerInterface $logger
    ) {
        $this->securityFacade = $securityFacade;
        $this->logger = $logger;
    }

    /**
     * Checks if an access to a controller action is granted or not.
     *
     * This method is executed just before any controller action.
     *
     * @throws AccessDeniedException
     */
    public function onKernelController(ControllerEvent $event): void
    {
        $controller = $event->getController();
        /*
         * $controller passed can be either a class or a Closure. This is not usual in Symfony2 but it may happen.
         * If it is a class, it comes in array format
         */
        if (is_array($controller)) {
            [$object, $method] = $controller;
            $controllerClass = ClassUtils::getClass($object);

            $this->logger->debug(
                sprintf(
                    'Invoked controller "%s::%s". (%s)',
                    $controllerClass,
                    $method,
                    $event->getRequestType() === HttpKernelInterface::MAIN_REQUEST ? 'MASTER_REQUEST' : 'SUB_REQUEST'
                )
            );

            if (!$this->securityFacade->isClassMethodGranted($controllerClass, $method) &&
                $event->getRequestType() === HttpKernelInterface::MAIN_REQUEST
            ) {
                throw AccessDeniedException::create($controllerClass, $method);
            }
        }
    }
}
