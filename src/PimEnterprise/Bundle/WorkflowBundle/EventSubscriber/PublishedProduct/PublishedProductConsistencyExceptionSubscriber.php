<?php

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2014 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace PimEnterprise\Bundle\WorkflowBundle\EventSubscriber\PublishedProduct;

use PimEnterprise\Bundle\WorkflowBundle\Exception\PublishedProductConsistencyException;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpKernel\Event\GetResponseForExceptionEvent;
use Symfony\Component\HttpKernel\KernelEvents;
use Symfony\Component\Routing\Router;

/**
 * Subscriber when PublishedProductConsistencyException is thrown as response
 *
 * @author Romain Monceau <romain@akeneo.com>
 */
class PublishedProductConsistencyExceptionSubscriber implements EventSubscriberInterface
{
    /** @var Router */
    protected $router;

    /**
     * @param Router $router
     */
    public function __construct(Router $router)
    {
        $this->router = $router;
    }

    /**
     * {@inheritdoc}
     */
    public static function getSubscribedEvents()
    {
        return [
            KernelEvents::EXCEPTION => 'onKernelException'
        ];
    }

    /**
     * Check the exception and redirect if needed
     *
     * @param GetResponseForExceptionEvent $event
     */
    public function onKernelException(GetResponseForExceptionEvent $event)
    {
        $e = $event->getException();
        if ($e instanceof PublishedProductConsistencyException) {
            // Only work if the url matching the _route is also accessible through GET
            if (null !== $e->getRoute()) {
                $response = new RedirectResponse($this->router->generate($e->getRoute(), $e->getRouteParams()));
                $event->setResponse($response);
                $event->getRequest()->getSession()->getFlashBag()->add('error', $e->getMessage());
            }
        }
    }
}
