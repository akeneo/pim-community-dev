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
use Pim\Component\Catalog\Model\LocaleInterface;
use PimEnterprise\Component\ActivityManager\Model\ProjectInterface;
use PimEnterprise\Component\ActivityManager\Remover\ProjectRemoverEngine;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\EventDispatcher\GenericEvent;

/**
 * The goal of this subscriber is to listen on entities pre remove events to be able to know if this entity removing
 * has impact on projects. If it's the case, it removes relevant projects.
 *
 * @author Willy Mesnage <willy.mesnage@akeneo.com>
 */
class ProjectRemoverSubscriber implements EventSubscriberInterface
{
    /** @var ProjectRemoverEngine */
    protected $projectRemoverEngine;

    /**
     * @param ProjectRemoverEngine $projectRemoverEngine
     */
    public function __construct(ProjectRemoverEngine $projectRemoverEngine)
    {
        $this->projectRemoverEngine = $projectRemoverEngine;
    }

    /**
     * {@inheritdoc}
     */
    public static function getSubscribedEvents()
    {
        return [
            StorageEvents::PRE_REMOVE => 'removeProjects',
            StorageEvents::POST_SAVE => 'removeProjectsFromDeactivatedLocale'
        ];
    }

    /**
     * @param GenericEvent $event
     */
    public function removeProjects(GenericEvent $event)
    {
        $entity = $event->getSubject();
        if ($entity instanceof ProjectInterface) {
            return;
        }
        $this->projectRemoverEngine->remove($entity);
    }

    /**
     * Removes projects impacted by a locale deactivation.
     *
     * @param GenericEvent $event
     */
    public function removeProjectsFromDeactivatedLocale(GenericEvent $event)
    {
        $locale = $event->getSubject();
        if (!$locale instanceof LocaleInterface) {
            return;
        }
        $this->projectRemoverEngine->remove($locale);
    }
}
