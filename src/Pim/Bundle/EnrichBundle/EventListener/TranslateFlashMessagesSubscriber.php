<?php

namespace Pim\Bundle\EnrichBundle\EventListener;

use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpKernel\KernelEvents;
use Symfony\Component\HttpKernel\Event\FilterResponseEvent;
use Symfony\Component\Translation\TranslatorInterface;
use Symfony\Component\HttpKernel\HttpKernelInterface;
use Pim\Bundle\EnrichBundle\Flash\Message;

/**
 * Translate all flash messages
 *
 * @author    Gildas Quemener <gildas@akeneo.com>
 * @copyright 2014 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class TranslateFlashMessagesSubscriber implements EventSubscriberInterface
{
    /** @var TranslatorInterface */
    protected $translator;

    /**
     * @param TranslatorInterface $translator
     */
    public function __construct(TranslatorInterface $translator)
    {
        $this->translator = $translator;
    }

    /**
     * {@inheritdoc}
     *
     * All subscribers to the response event registered with a higher priority will have access to Message instance
     * inside the flash bag. All others will have access to strings (translated flashes).
     */
    public static function getSubscribedEvents()
    {
        return [
            KernelEvents::RESPONSE => ['translate', -128],
        ];
    }

    /**
     * Replace flash messages with their translation
     *
     * @param FilterResponseEvent $event
     */
    public function translate(FilterResponseEvent $event)
    {
        $bag = $event->getRequest()->getSession()->getFlashBag();
        $messages = [];
        foreach ($bag->all() as $type => $flashes) {
            foreach ($flashes as $flash) {
                if ($flash instanceof Message) {
                    $flash = $this->translator->trans(
                        $flash->getTemplate(),
                        $flash->getParameters()
                    );
                }
                $messages[$type][] = $flash;
            }
        }

        $bag->setAll($messages);
    }
}
