<?php

namespace Pim\Bundle\UserBundle\EventSubscriber;

use Symfony\Component\Security\Http\Event\InteractiveLoginEvent;

/**
 * Locale Subscriber
 *
 * @author    Pierre Allard <pierre.allard@akeneo.com>
 * @copyright 2015 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class LocaleSubscriber
{
    /**
     * @param InteractiveLoginEvent $event
     */
    public function onSecurityInteractiveLogin(InteractiveLoginEvent $event)
    {
        $user = $event->getAuthenticationToken()->getUser();

        $event->getRequest()->getSession()->set('_locale', $user->getUiLocale()->getLanguage());
    }
}
