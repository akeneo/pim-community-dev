<?php

namespace Pim\Bundle\UserBundle\EventSubscriber;

use Pim\Bundle\UserBundle\Event\UserEvent;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\EventDispatcher\GenericEvent;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpKernel\Event\GetResponseEvent;
use Symfony\Component\HttpKernel\KernelEvents;
use Symfony\Component\Translation\TranslatorInterface;

/**
 * Locale Subscriber
 *
 * @author    Olivier Soulet <olivier.soulet@akeneo.com>
 * @copyright 2015 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class LocaleSubscriber implements EventSubscriberInterface
{
    /** @var RequestStack */
    protected $requestStack;

    /** @var TranslatorInterface */
    protected $translator;

    /**
     * @param RequestStack        $requestStack
     * @param TranslatorInterface $translator
     */
    public function __construct(RequestStack $requestStack, TranslatorInterface $translator)
    {
        $this->requestStack = $requestStack;
        $this->translator   = $translator;
    }

    /**
     * @param GenericEvent $event
     */
    public function onPostUpdate(GenericEvent $event)
    {
        $user = $event->getSubject();

        if ($user === $event->getArgument('current_user')) {
            $request = $this->requestStack->getMasterRequest();
            $request->getSession()->set('_locale', $user->getUiLocale()->getLanguage());
            $this->translator->setLocale($user->getUiLocale()->getLanguage());
        }
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
     * @return array
     */
    public static function getSubscribedEvents()
    {
        return [
            UserEvent::POST_UPDATE => [['onPostUpdate']],
            KernelEvents::REQUEST  => [['onKernelRequest', 17]],
        ];
    }
}
