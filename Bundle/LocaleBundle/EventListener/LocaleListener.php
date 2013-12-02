<?php

namespace Oro\Bundle\LocaleBundle\EventListener;

use Symfony\Component\HttpKernel\Event\GetResponseEvent;
use Symfony\Component\HttpKernel\KernelEvents;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

use Oro\Bundle\LocaleBundle\Model\LocaleSettings;

class LocaleListener implements EventSubscriberInterface
{
    /**
     * @var LocaleSettings
     */
    protected $localeSettings;

    /**
     * @var bool
     */
    protected $isInstalled;

    /**
     * @param LocaleSettings $localeSettings
     * @param string|bool|null $installed
     */
    public function __construct(LocaleSettings $localeSettings, $installed)
    {
        $this->localeSettings = $localeSettings;
        $this->isInstalled = !empty($installed);
    }

    /**
     * @param Request $request
     */
    public function setRequest(Request $request = null)
    {
        if (!$request) {
            return;
        }

        if ($this->isInstalled) {
            if (!$request->attributes->get('_locale')) {
                $request->setLocale($this->localeSettings->getLanguage());
            }
            $this->setPhpDefaultLocale($this->localeSettings->getLocale());
        }
    }

    /**
     * @param GetResponseEvent $event
     */
    public function onKernelRequest(GetResponseEvent $event)
    {
        $request = $event->getRequest();
        $this->setRequest($request);
    }

    /**
     * @param string $locale
     */
    public function setPhpDefaultLocale($locale)
    {
        \Locale::setDefault($locale);
    }

    /**
     * @return array
     */
    public static function getSubscribedEvents()
    {
        return array(
            // must be registered after Symfony's original LocaleListener
            KernelEvents::REQUEST => array(array('onKernelRequest', 15)),
        );
    }
}
