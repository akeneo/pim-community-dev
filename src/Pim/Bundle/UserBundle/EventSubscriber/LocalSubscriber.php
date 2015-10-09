<?php

namespace Pim\Bundle\UserBundle\EventSubscriber;

use Pim\Bundle\UserBundle\Event\UserEvent;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\EventDispatcher\GenericEvent;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpKernel\Event\GetResponseEvent;
use Symfony\Component\HttpKernel\KernelEvents;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Http\Event\InteractiveLoginEvent;

/**
 * Locale Listener
 *
 * @author    Olivier Soulet <olivier.soulet@akeneo.com>
 * @copyright 2015 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class LocalSubscriber implements EventSubscriberInterface
{
    /** @var RequestStack */
    protected $requestStack;
    /**
     * @param TokenStorageInterface $tokenStorage
     */
    public function __construct(RequestStack $requestStack)
    {
        $this->requestStack = $requestStack;
    }

    /**
     * @param GenericEvent $event
     */
    public function onPostUpdate(GenericEvent $event)
    {
        $user = $event->getSubject();
        $request = $this->requestStack->getMasterRequest();
        $locale = $user->getUiLocale()->getLanguage();

        $request->setLocale($locale);
        $request->getSession()->set('_locale', $locale);
        $request->attributes->set('_locale', $locale);
    }

    /**
     * @param GetResponseEvent $event
     */
    public function onKernelRequest(GetResponseEvent $event)
    {
        $request = $event->getRequest();

        $request->setLocale($request->getSession()->get('_locale'));
    }

    /**
     * @param InteractiveLoginEvent $event
     */
    public function onSecurityInteractiveLogin(InteractiveLoginEvent $event)
    {
        $user = $event->getAuthenticationToken()->getUser();

        $event->getRequest()->getSession()->set('_locale', $user->getUiLocale()->getLanguage());
    }

    /**
     * @return array
     */
    public static function getSubscribedEvents()
    {
        return [
            UserEvent::POST_UPDATE => [['onPostUpdate', 17]],
            KernelEvents::REQUEST  => [['onKernelRequest', 17]],
        ];
    }
}
