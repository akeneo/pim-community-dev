<?php

declare(strict_types=1);

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2016 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace PimEnterprise\Bundle\CatalogRuleBundle\EventSubscriber;

use Akeneo\Bundle\RuleEngineBundle\Event\RuleEvents;
use Pim\Bundle\NotificationBundle\Entity\NotificationInterface;
use Pim\Bundle\NotificationBundle\NotifierInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\EventDispatcher\GenericEvent;
use Symfony\Component\Security\Core\Exception\UsernameNotFoundException;
use Symfony\Component\Security\Core\User\ChainUserProvider;
use Symfony\Component\Security\Core\User\UserInterface;

/**
 * @author Damien Carcel <damien.carcel@gmail.com>
 */
class RuleExecutionSubscriber implements EventSubscriberInterface
{
    /** @var ChainUserProvider */
    protected $chainUserProvider;

    /** @var NotifierInterface */
    protected $notifier;

    /** @var string */
    protected $notificationClass;

    /**
     * @param ChainUserProvider     $chainUserProvider
     * @param NotifierInterface     $notifier
     * @param string                $notificationClass
     */
    public function __construct(
        ChainUserProvider $chainUserProvider,
        NotifierInterface $notifier,
        $notificationClass
    ) {
        $this->chainUserProvider = $chainUserProvider;
        $this->notifier = $notifier;
        $this->notificationClass = $notificationClass;
    }

    /**
     * {@inheritdoc}
     */
    public static function getSubscribedEvents(): array
    {
        return [
            RuleEvents::POST_EXECUTE_ALL => 'afterJobExecution',
        ];
    }

    /**
     * Notify a user of the end of the command.
     *
     * @param GenericEvent $event
     */
    public function afterJobExecution(GenericEvent $event): void
    {
        $rules = $event->getSubject();
        $user = $this->getUserFromEvent($event);

        if (null === $user || 0 === count($rules)) {
            return;
        }

        $notification = $this->createNotification();
        $this->notifier->notify($notification, [$user]);
    }

    /**
     * @return NotificationInterface
     */
    protected function createNotification(): NotificationInterface
    {
        $notification = new $this->notificationClass();
        $notification
            ->setType('success')
            ->setMessage('pimee_catalog_rule.notification.rule.executed')
            ->setRoute('pimee_catalog_rule_rule_index')
            ->setContext([
                'actionType'       => 'rule',
                'showReportButton' => false,
            ]);

        return $notification;
    }

    /**
     * @param GenericEvent $event
     *
     * @return null|UserInterface
     */
    protected function getUserFromEvent(GenericEvent $event): ?UserInterface
    {
        try {
            $userToNotify = $event->getArgument('username');
        } catch (\InvalidArgumentException $e) {
            return null;
        }

        try {
            return $this->chainUserProvider->loadUserByUsername($userToNotify);
        } catch (UsernameNotFoundException $e) {
            return null;
        }
    }
}
