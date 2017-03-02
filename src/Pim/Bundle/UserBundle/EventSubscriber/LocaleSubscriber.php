<?php

namespace Pim\Bundle\UserBundle\EventSubscriber;

use Doctrine\ORM\EntityManager;
use FOS\RestBundle\FOSRestBundle;
use Pim\Bundle\UserBundle\Event\UserEvent;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\EventDispatcher\GenericEvent;
use Symfony\Component\HttpFoundation\Request;
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

    /** @var EntityManager */
    protected $em;

    /**
     * @param RequestStack        $requestStack
     * @param TranslatorInterface $translator
     * @param EntityManager       $em
     */
    public function __construct(RequestStack $requestStack, TranslatorInterface $translator, EntityManager $em)
    {
        $this->requestStack = $requestStack;
        $this->translator = $translator;
        $this->em = $em;
    }

    /**
     * @param GenericEvent $event
     */
    public function onPostUpdate(GenericEvent $event)
    {
        $request = $event->getRequest();

        if (!$request->attributes->has(FOSRestBundle::ZONE_ATTRIBUTE)) {
            return;
        }

        $user = $event->getSubject();

        if ($user === $event->getArgument('current_user')) {
            $request = $this->requestStack->getMasterRequest();
            $request->getSession()->set('_locale', $user->getUiLocale()->getCode());
            $this->translator->setLocale($user->getUiLocale()->getCode());
            $request->getSession()->save();
        }
    }

    /**
     * @param GetResponseEvent $event
     */
    public function onKernelRequest(GetResponseEvent $event)
    {
        $request = $event->getRequest();

        if ($request->attributes->has(FOSRestBundle::ZONE_ATTRIBUTE)) {
            return;
        }

        $locale = $this->getLocale($request);

        if (null !== $locale) {
            $request->setLocale($locale);
        }
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

    /**
     * @param Request $request
     *
     * @return string|null
     */
    protected function getLocale(Request $request)
    {
        if (!$request->hasPreviousSession()) {
            return $this->getLocaleFromOroConfigValue();
        }

        $locale = null !== $request->getSession()->get('_locale') ?
            $request->getSession()->get('_locale') : $this->getLocaleFromOroConfigValue();

        $request->getSession()->save();

        return $locale;
    }

    /**
     * @return string|null
     */
    protected function getLocaleFromOroConfigValue()
    {
        $locale = $this->em
            ->getRepository('OroConfigBundle:ConfigValue')
            ->getSectionForEntityAndScope('pim_localization', 'app', 0);

        return null === $locale ? null : $locale->getValue();
    }
}
