<?php

namespace Oro\Bundle\InstallerBundle\EventListener;

use Symfony\Component\HttpKernel\Event\GetResponseEvent;
use Symfony\Component\HttpKernel\HttpKernel;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\Routing\Router;

class RequestListener
{
    /**
     * Installed flag
     *
     * @var bool
     */
    protected $installed;

    /**
     * @var Router
     */
    protected $router;

    /**
     *
     */
    public function __construct($installed, Router $router)
    {
        $this->installed = $installed;
        $this->router    = $router;
    }

    public function onRequest(GetResponseEvent $event)
    {
        if (HttpKernel::MASTER_REQUEST != $event->getRequestType()) {
            return;
        }

        $allowedRoutes = array(
            'oro_installer_flow',
            'oro_translation_jstranslation',
            'sylius_flow_display',
            'sylius_flow_forward',
            'fos_js_routing_js',
            '_wdt',
        );

        if (!$this->installed) {
            if (!in_array($event->getRequest()->get('_route'), $allowedRoutes)) {
                $event->setResponse(new RedirectResponse($this->router->generate('oro_installer_flow')));
            }

            $event->stopPropagation();
        }
    }
}
