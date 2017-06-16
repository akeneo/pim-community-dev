<?php

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
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Core\User\UserInterface;

/**
 * @author Damien Carcel <damien.carcel@gmail.com>
 */
class RuleExecutionSubscriber implements EventSubscriberInterface
{
    /** @var TokenStorageInterface */
    protected $tokenStorage;

    /** @var NotifierInterface */
    protected $notifier;

    /** @var string */
    protected $notificationClass;

    /**
     * @param TokenStorageInterface $tokenStorage
     * @param NotifierInterface     $notifier
     * @param string                $notificationClass
     */
    public function __construct(TokenStorageInterface $tokenStorage, NotifierInterface $notifier, $notificationClass)
    {
        $this->tokenStorage = $tokenStorage;
        $this->notifier = $notifier;
        $this->notificationClass = $notificationClass;
    }

    /**
     * {@inheritdoc}
     */
    public static function getSubscribedEvents()
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
    public function afterJobExecution(GenericEvent $event)
    {
        $rules = $event->getSubject();
        $user = $this->getUser();

        if (null === $user || 0 === count($rules)) {
            return;
        }

        $notification = $this->createNotification();
        $this->notifier->notify($notification, [$user]);
    }

    /**
     * @return NotificationInterface
     */
    protected function createNotification()
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
     * @return UserInterface|null
     */
    protected function getUser()
    {
        $user = null;

        if (null !== $token = $this->tokenStorage->getToken()) {
            $user = $token->getUser();
        };

        return $user;
    }
}
