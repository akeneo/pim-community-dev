<?php

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2016 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace PimEnterprise\Bundle\ActivityManagerBundle\EventListener;

use Akeneo\Component\StorageUtils\StorageEvents;
use PimEnterprise\Component\ActivityManager\Model\ProjectInterface;
use PimEnterprise\Component\ActivityManager\Remover\ChainedProjectRemover;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\EventDispatcher\GenericEvent;

/**
 * The goal of this subscriber is to listen on catalog updates events to be able to know if entities updates/removing
 * has impact on projects. If it's the case, it removes relevant projects.
 *
 * @author Willy Mesnage <willy.mesnage@akeneo.com>
 */
class CatalogUpdatesSubscriber implements EventSubscriberInterface
{
    /** @var ChainedProjectRemover */
    protected $chainedProjectRemover;

    /**
     * @param ChainedProjectRemover $chainedProjectRemover
     */
    public function __construct(ChainedProjectRemover $chainedProjectRemover)
    {
        $this->chainedProjectRemover = $chainedProjectRemover;
    }

    /**
     * {@inheritdoc}
     */
    public static function getSubscribedEvents()
    {
        return [
            StorageEvents::PRE_REMOVE => 'removeProjectsImpactedByEntity',
            StorageEvents::POST_SAVE  => 'removeProjectsImpactedByEntity'
        ];
    }

    /**
     * @param GenericEvent $event
     * @param string       $eventName
     */
    public function removeProjectsImpactedByEntity(GenericEvent $event, $eventName)
    {
        $entity = $event->getSubject();
        if ($entity instanceof ProjectInterface) {
            return;
        }
        $this->chainedProjectRemover->removeProjectsImpactedBy($entity, $eventName);
    }
}
