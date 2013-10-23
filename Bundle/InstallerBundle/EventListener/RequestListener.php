<?php

namespace Oro\Bundle\InstallerBundle\EventListener;

use Symfony\Component\HttpKernel\Event\GetResponseEvent;
use Symfony\Component\HttpKernel\HttpKernel;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\Routing\Router;

class RequestListener
{
    /**
     * @var Router
     */
    protected $router;

    /**
     * Installed flag
     *
     * @var bool
     */
    protected $installed;

    /**
     * Debug flag
     *
     * @var bool
     */
    protected $debug;

    /**
     *
     */
    public function __construct(Router $router, $installed, $debug = false)
    {
        $this->router    = $router;
        $this->installed = $installed;
        $this->debug     = $debug;
    }

    public function onRequest(GetResponseEvent $event)
    {
        if (HttpKernel::MASTER_REQUEST != $event->getRequestType()) {
            return;
        }

        $allowedRoutes = array(
            'oro_installer_flow',
            'sylius_flow_display',
            'sylius_flow_forward',
        );

        if ($this->debug) {
            $allowedRoutes = array_merge(
                $allowedRoutes,
                array(
                    '_wdt',
                    '_profiler',
                    '_profiler_search',
                    '_profiler_search_bar',
                    '_profiler_search_results',
                    '_profiler_router',
                )
            );
        }

        if (!$this->installed) {
            if (!in_array($event->getRequest()->get('_route'), $allowedRoutes)) {
                $event->setResponse(new RedirectResponse($event->getRequest()->getBasePath() . '/install.php'));
            }

            $event->stopPropagation();
        }
    }
}
