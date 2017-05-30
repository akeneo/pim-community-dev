<?php

namespace Oro\Bundle\SecurityBundle\EventListener;

use Doctrine\Common\Util\ClassUtils;
use Oro\Bundle\SecurityBundle\Exception\AccessDeniedException;
use Oro\Bundle\SecurityBundle\SecurityFacade;
use Psr\Log\LoggerInterface;
use Symfony\Component\HttpKernel\Event\FilterControllerEvent;
use Symfony\Component\HttpKernel\HttpKernelInterface;

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
     * @param SecurityFacade  $securityFacade
     * @param LoggerInterface $logger
     */
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
     * @param  FilterControllerEvent $event
     *
     * @throws AccessDeniedException
     */
    public function onKernelController(FilterControllerEvent $event)
    {
        $controller = $event->getController();
        /*
         * $controller passed can be either a class or a Closure. This is not usual in Symfony2 but it may happen.
         * If it is a class, it comes in array format
         */
        if (is_array($controller)) {
            list($object, $method) = $controller;
            $controllerClass = ClassUtils::getClass($object);

            $this->logger->debug(
                sprintf(
                    'Invoked controller "%s::%s". (%s)',
                    $controllerClass,
                    $method,
                    $event->getRequestType() === HttpKernelInterface::MASTER_REQUEST ? 'MASTER_REQUEST' : 'SUB_REQUEST'
                )
            );

            if (!$this->securityFacade->isClassMethodGranted($controllerClass, $method) &&
                $event->getRequestType() === HttpKernelInterface::MASTER_REQUEST
            ) {
                throw AccessDeniedException::create($controllerClass, $method);
            }
        }
    }
}
