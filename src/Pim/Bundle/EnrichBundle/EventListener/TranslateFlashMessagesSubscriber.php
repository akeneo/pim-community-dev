<?php

namespace Pim\Bundle\EnrichBundle\EventListener;

use Pim\Bundle\EnrichBundle\Flash\Message;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpKernel\Event\KernelEvent;
use Symfony\Component\HttpKernel\KernelEvents;
use Symfony\Component\Translation\TranslatorInterface;

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
     * Translation of flash messages must have a high priority to be sure that flashes bag is not emptied by any other
     * subscriber. For example, the symfony view listener will render a template given some parameters returned by
     * the controller action. If this template peeks all the flash messages before they have been translated, then it's
     * too late.
     */
    public static function getSubscribedEvents()
    {
        return [
            KernelEvents::VIEW     => ['translate', 128],
            KernelEvents::RESPONSE => ['translate', 128],
        ];
    }

    /**
     * Replace flash messages with their translation
     *
     * @param KernelEvent $event
     */
    public function translate(KernelEvent $event)
    {
        if (!$event->getRequest()->hasSession()) {
            return;
        }

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
