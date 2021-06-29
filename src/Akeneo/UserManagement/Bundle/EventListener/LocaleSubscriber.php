<?php

namespace Akeneo\UserManagement\Bundle\EventListener;

use Akeneo\UserManagement\Component\Event\UserEvent;
use Doctrine\ORM\EntityManager;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\EventDispatcher\GenericEvent;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpKernel\Event\RequestEvent;
use Symfony\Component\HttpKernel\KernelEvents;
use Symfony\Component\Security\Http\Event\InteractiveLoginEvent;
use Symfony\Component\Security\Http\SecurityEvents;
use Symfony\Contracts\Translation\LocaleAwareInterface;

/**
 * Locale Subscriber
 *
 * @author    Olivier Soulet <olivier.soulet@akeneo.com>
 * @copyright 2015 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class LocaleSubscriber implements EventSubscriberInterface
{
    protected RequestStack $requestStack;
    protected LocaleAwareInterface $localeAware;
    protected EntityManager $em;

    public function __construct(RequestStack $requestStack, LocaleAwareInterface $localeAware, EntityManager $em)
    {
        $this->requestStack = $requestStack;
        $this->localeAware = $localeAware;
        $this->em = $em;
    }

    /**
     * @param GenericEvent $event
     */
    public function onPostUpdate(GenericEvent $event)
    {
        $user = $event->getSubject();

        if ($user === $event->getArgument('current_user')) {
            $request = $this->requestStack->getMasterRequest();
            $request->getSession()->set('_locale', $user->getUiLocale()->getCode());
            $this->localeAware->setLocale($user->getUiLocale()->getCode());
        }
    }

    public function onKernelRequest(RequestEvent $event)
    {
        $request = $event->getRequest();
        $locale = $this->getLocale($request);

        if (null !== $locale) {
            $request->setLocale($locale);
        }
    }

    /**
     * @param InteractiveLoginEvent $event
     */
    public function onSecurityInteractiveLogin(InteractiveLoginEvent $event)
    {
        $user = $event->getAuthenticationToken()->getUser();

        $event->getRequest()->getSession()->remove('dataLocale');
        $event->getRequest()->getSession()->set('_locale', $user->getUiLocale()->getCode());
    }

    /**
     * @return array
     */
    public static function getSubscribedEvents()
    {
        return [
            UserEvent::POST_UPDATE => [['onPostUpdate']],
            KernelEvents::REQUEST  => [['onKernelRequest', 17]],
            SecurityEvents::INTERACTIVE_LOGIN  => [['onSecurityInteractiveLogin']],
        ];
    }

    /**
     * @param Request $request
     *
     * @return string|null
     */
    protected function getLocale(Request $request)
    {
        return null !== $request->getSession() && null !== $request->getSession()->get('_locale') ?
            $request->getSession()->get('_locale') : $this->getLocaleFromOroConfigValue();
    }

    /**
     * @return string|null
     */
    protected function getLocaleFromOroConfigValue()
    {
        $sql = 'SELECT value FROM oro_config_value WHERE name = "language" AND section = "pim_ui" LIMIT 1';
        $statement = $this->em->getConnection()->executeQuery($sql);
        $locale = $statement->fetchColumn(0);

        if (!$locale) {
            return null;
        }

        return $locale;
    }
}
