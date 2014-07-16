<?php

namespace PimEnterprise\Bundle\WorkflowBundle\EventSubscriber\PublishedProduct;

use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpKernel\Event\GetResponseForExceptionEvent;
use Symfony\Component\HttpKernel\KernelEvents;
use Symfony\Component\Routing\Router;
use PimEnterprise\Bundle\WorkflowBundle\Exception\PublishedProductConsistencyException;

/**
 * Subscriber when PublishedProductConsistencyException is thrown as response
 *
 * @author    Romain Monceau <romain@akeneo.com>
 * @copyright 2014 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
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
        if ($e instanceof PublishedProductConsistencyException && $e->needsRedirection()) {
            $request = $event->getRequest();
            $response = new RedirectResponse(
                $this->router->generate(
                    $request->attributes->get('_route'),
                    $request->attributes->get('_route_params')
                )
            );
            $event->setResponse($response);
            $request->getSession()->getFlashBag()->add('error', $e->getMessage());
        }
    }
}
