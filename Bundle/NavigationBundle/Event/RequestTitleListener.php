<?php

namespace Oro\Bundle\NavigationBundle\Event;

use Oro\Bundle\NavigationBundle\Provider\TitleServiceInterface;
use Symfony\Component\HttpKernel\Event\GetResponseEvent;
use Symfony\Component\HttpKernel\HttpKernel;

class RequestTitleListener
{
    /**
     * @var TitleServiceInterface
     */
    private $service;

    /**
     * Injection
     *
     * @param TitleServiceInterface $service
     */
    public function __construct(TitleServiceInterface $service)
    {
        $this->service = $service;
    }

    /**
     * Find title for current route in database
     *
     * @param GetResponseEvent $event
     */
    public function onKernelRequest(GetResponseEvent $event)
    {
        $request = $event->getRequest();
        if (HttpKernel::MASTER_REQUEST != $event->getRequestType()
            || $request->getRequestFormat() != 'html'
            || ($request->getMethod() != 'GET' && !$request->headers->get('x-oro-hash-navigation'))
            || ($request->isXmlHttpRequest() && !$request->headers->get('x-oro-hash-navigation'))
        ) {
            // don't do anything
            return;
        }

        $route = $request->get('_route');

        $this->service->loadByRoute($route);
    }
}
