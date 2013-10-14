<?php

namespace Oro\Bundle\HelpBundle\EventListener;

use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpKernel\HttpKernel;
use Symfony\Component\HttpKernel\Event\GetResponseEvent;
use Symfony\Component\HttpKernel\Event\FilterResponseEvent;
use Symfony\Component\HttpKernel\Event\FilterControllerEvent;
use Symfony\Component\HttpFoundation\Request;

use Oro\Bundle\HelpBundle\Annotation\Help;
use Oro\Bundle\HelpBundle\Model\HelpLinkProvider;

class HelpLinkRequestListener
{
    /**
     * @var ContainerInterface
     */
    protected $container;

    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
    }

    public function onKernelRequest(GetResponseEvent $event)
    {
        return;
    }

    public function onKernelResponse(FilterResponseEvent $event)
    {
        return;
    }

    public function onKernelController(FilterControllerEvent $event)
    {
        if (HttpKernel::MASTER_REQUEST == $event->getRequestType()) {
            $this->initHelpLinkProvider($event->getRequest());
        }

        return;
    }

    protected function initHelpLinkProvider(Request $request)
    {
        $provider = $this->getHelpLinkProvider();
        $provider->setRequestController($request->get('_controller'));
        $provider->setHelpConfigurationAnnotation($request->get('_' . Help::ALIAS));
    }

    /**
     * @return HelpLinkProvider
     */
    protected function getHelpLinkProvider()
    {
        return $this->container->get('oro_help.model.help_link_provider');
    }
}
