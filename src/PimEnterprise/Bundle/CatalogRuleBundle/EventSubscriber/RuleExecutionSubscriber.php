<?php

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2014 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace PimEnterprise\Bundle\CatalogRuleBundle\EventSubscriber;

use Akeneo\Bundle\RuleEngineBundle\Event\RuleEvents;
use Pim\Bundle\NotificationBundle\Entity\NotificationInterface;
use Pim\Bundle\NotificationBundle\Factory\NotificationFactoryRegistry;
use Pim\Bundle\NotificationBundle\NotifierInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\EventDispatcher\GenericEvent;

/**
 * @author Damien Carcel <damien.carcel@gmail.com>
 */
class RuleExecutionSubscriber implements EventSubscriberInterface
{
    /** @var NotificationFactoryRegistry */
    protected $factoryRegistry;

    /** @var NotifierInterface */
    protected $notifier;

    /**
     * @param NotificationFactoryRegistry $factoryRegistry
     * @param NotifierInterface           $notifier
     */
    public function __construct(
        NotificationFactoryRegistry $factoryRegistry,
        NotifierInterface $notifier
    ) {
        $this->factoryRegistry = $factoryRegistry;
        $this->notifier        = $notifier;
    }

    /**
     * {@inheritdoc}
     */
    public static function getSubscribedEvents()
    {
        return [
            RuleEvents::AFTER_COMMAND_EXECUTION => 'afterJobExecution',
        ];
    }

    /**
     * Notify a user of the end of the command.
     *
     * @param GenericEvent $event
     */
    public function afterJobExecution(GenericEvent $event)
    {
        $rules = $event->getSubject();
        $user  = $event->hasArgument('user') ? $event->getArgument('user') : null;
        $count = count($rules);

        if (null === $user || 0 === $count) {
            return;
        }

        $notification = $this->createNotification($count);
        $this->notifier->notify($notification, [$user]);
    }

    /**
     * @param string $count
     *
     * @throws \LogicException
     *
     * @return NotificationInterface
     */
    protected function createNotification($count)
    {
        $factory = $this->factoryRegistry->get('rule');

        if (null === $factory) {
            throw new \LogicException(sprintf('No notification factory found for the "%s" job type', 'rule'));
        }

        $notification = $factory->create($count);

        return $notification;
    }
}
