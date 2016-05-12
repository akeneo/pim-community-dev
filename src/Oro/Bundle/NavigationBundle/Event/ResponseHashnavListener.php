<?php
namespace Oro\Bundle\NavigationBundle\Event;

use Symfony\Bundle\FrameworkBundle\Templating\EngineInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpKernel\Event\FilterResponseEvent;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

class ResponseHashnavListener
{
    const HASH_NAVIGATION_HEADER = 'x-oro-hash-navigation';

    /**
     * @var ContainerInterface
     */
    private $container;

    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
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
            $isFullRedirect = $response->headers->get('oroFullRedirect', false);
            if ($response->isRedirect()) {
                $location = $response->headers->get('location');
                if (!is_object($this->getTokenStorage()->getToken())) {
                    $isFullRedirect = true;
                }
            }
            if ($response->isNotFound()) {
                $location = $request->getUri();
                $isFullRedirect = true;
            }
            if ($location) {
                $event->setResponse(
                    $this->getTemplating()->renderResponse(
                        'OroNavigationBundle:HashNav:redirect.html.twig',
                        [
                            'full_redirect' => $isFullRedirect,
                            'location'      => $location,
                        ]
                    )
                );
            }
        }
    }

    /**
     * @return TokenStorageInterface
     */
    final protected function getTokenStorage()
    {
        return $this->container->get('security.token_storage');
    }

    /**
     * @return EngineInterface
     */
    final protected function getTemplating()
    {
        return $this->container->get('templating');
    }
}
