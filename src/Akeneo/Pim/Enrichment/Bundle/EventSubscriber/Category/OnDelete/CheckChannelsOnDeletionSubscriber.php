<?php

namespace Akeneo\Pim\Enrichment\Bundle\EventSubscriber\Category\OnDelete;

use Akeneo\Pim\Enrichment\Component\Category\Model\CategoryInterface;
use Akeneo\Tool\Component\StorageUtils\StorageEvents;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\EventDispatcher\GenericEvent;
use Symfony\Component\HttpKernel\Exception\ConflictHttpException;
use Symfony\Component\Translation\TranslatorInterface;

/**
 * Check if the category is used by a channel when try to remove it
 *
 * @author    Romain Monceau <romain@akeneo.com>
 * @copyright 2014 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
final class CheckChannelsOnDeletionSubscriber implements EventSubscriberInterface
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
     */
    public static function getSubscribedEvents()
    {
        return [
            StorageEvents::PRE_REMOVE => 'checkChannels'
        ];
    }

    /**
     * Check if channels are linked to this tree
     *
     * @param GenericEvent $event
     *
     * @throws ConflictHttpException
     */
    public function checkChannels(GenericEvent $event)
    {
        $subject = $event->getSubject();

        if (!$subject instanceof CategoryInterface || !$subject->isRoot()) {
            return;
        }

        if (count($subject->getChannels()) > 0) {
            throw new ConflictHttpException($this->translator->trans('flash.tree.not removable'));
        }
    }
}
