<?php

namespace Akeneo\Platform\Bundle\UIBundle\EventListener;

use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\Session\Storage\Handler\NativeSessionHandler;
use Symfony\Component\HttpKernel\Event\GetResponseEvent;
use Symfony\Component\HttpKernel\KernelEvents;

/**
 * Aims to close the session after that all listeners have been executed before calling the controller.
 *
 * Symfony opens automatically a session, for each request, at the very beginning. To open a session, it tries to get a lock.
 * If a concurrent request has already got the lock, the actual request is pending.
 * Therefore, it's not possible to execute concurrent requests due to this lock, which is very costly in term of performance.
 * Moreover, it can block the user interface if a request is taking too much time to execute.
 *
 * By default, Symfony writes the session data and close the session only at the end of the request.
 * By writing the session data and closing the session just before calling the controller,
 * it allows the concurrent request to access to the session data.
 *
 * Do note that if the controller reads or writes the session data, the session handler will automatically re-open the session.
 *
 * @author    Alexandre Hocquard <alexandre.hocquard@akeneo.com>
 * @copyright 2017 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class CloseSessionListener implements EventSubscriberInterface
{
    /** @var NativeSessionHandler */
    protected $sessionHandler;

    /**
     * {@inheritdoc}
     */
    public static function getSubscribedEvents()
    {
        return [
            KernelEvents::REQUEST => ['closeSession', -100]
        ];
    }

    /**
     * Save and close the session.
     *
     * @param GetResponseEvent $event
     */
    public function closeSession(GetResponseEvent $event) : void
    {
        if (!$event->getRequest()->hasSession()) {
            return;
        }

        $session = $event->getRequest()->getSession();
        if ($session->isStarted()) {
            $session->save();
        }
    }
}
