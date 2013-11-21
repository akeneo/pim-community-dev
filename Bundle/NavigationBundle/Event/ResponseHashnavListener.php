<?php
namespace Oro\Bundle\NavigationBundle\Event;

use Symfony\Component\HttpKernel\Event\FilterResponseEvent;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Core\SecurityContextInterface;
use Symfony\Bundle\FrameworkBundle\Templating\EngineInterface;

class ResponseHashnavListener
{

    const HASH_NAVIGATION_HEADER = 'x-oro-hash-navigation';

    /**
     * @var SecurityContextInterface
     */
    protected $security;

    /**
     * @var EngineInterface
     */
    protected $templating;

    public function __construct(SecurityContextInterface $security, EngineInterface $templating)
    {
        $this->security = $security;
        $this->templating = $templating;
    }

    /**
     * Checking request and response and decide whether we need a redirect
     *
     * @param FilterResponseEvent $event
     */
    public function onResponse(FilterResponseEvent $event)
    {
        $request = $event->getRequest();
        $response = $event->getResponse();
        if ($request->get(self::HASH_NAVIGATION_HEADER) || $request->headers->get(self::HASH_NAVIGATION_HEADER)) {
            $location = '';
            $isFullRedirect = false;
            if ($response->isRedirect()) {
                $location = $response->headers->get('location');
                if (!is_object($this->security->getToken())) {
                    $isFullRedirect = true;
                }
            }
            if ($response->isNotFound()) {
                $location = $request->getUri();
                $isFullRedirect = true;
            }
            if ($location) {
                $event->setResponse(
                    $this->templating->renderResponse(
                        'OroNavigationBundle:HashNav:redirect.html.twig',
                        array(
                            'full_redirect' => $isFullRedirect,
                            'location' => $location,
                        )
                    )
                );
            }
        }
    }
}
