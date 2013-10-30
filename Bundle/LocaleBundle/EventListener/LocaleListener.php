<?php

namespace Oro\Bundle\LocaleBundle\EventListener;

use Symfony\Component\HttpKernel\Event\GetResponseEvent;
use Symfony\Component\HttpKernel\KernelEvents;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

use Symfony\Component\Translation\TranslatorInterface;

use Oro\Bundle\LocaleBundle\Model\LocaleSettings;

class LocaleListener implements EventSubscriberInterface
{
    private $localeSettings;

    private $translator;

    public function __construct(LocaleSettings $localeSettings, TranslatorInterface $translator)
    {
        $this->localeSettings = $localeSettings;
        $this->translator = $translator;
    }

    public function setRequest(Request $request = null)
    {
        if (!$request) {
            return;
        }

        if (!$request->attributes->get('_locale')) {
            $request->setLocale($this->localeSettings->getLanguage());
        }
        $this->setPhpDefaultLocale($this->localeSettings->getLocale());
    }

    public function onKernelRequest(GetResponseEvent $event)
    {
        $request = $event->getRequest();
        $this->setRequest($request);
    }

    public function setPhpDefaultLocale($locale)
    {
        \Locale::setDefault($locale);
    }

    public static function getSubscribedEvents()
    {
        return array(
            // must be registered after Symfony's original LocaleListener
            KernelEvents::REQUEST => array(array('onKernelRequest', 15)),
        );
    }
}
